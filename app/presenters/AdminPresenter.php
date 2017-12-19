<?php

namespace App\Presenters;

use Components\LoginForm;
use Nette,
    App\Models,
    Nette\Application\UI,
    Nette\Utils\DateTime,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tracy\Debugger;


class AdminPresenter extends BasePresenter
{
    /**
     * @inject
     * @var Nette\Mail\SendmailMailer
     */
    public $sendmailMailer;

    /**
     * @inject
     * @var Nette\Security\User
     */
    public $user;

    /**
     * @inject
     * @var Models\CityModel
     */
    public $cityModel;

    /**
     * @inject
     * @var Models\CustomerModel
     */
    public $customerModel;

    /**
     * @inject
     * @var Models\TravelModel
     */
    public $travelModel;

    /**
     * @inject
     * @var Models\TravelTypeModel
     */
    public $travelTypeModel;

    /**
     * @inject
     * @var Models\TripModel
     */
    public $tripModel;

    public function actionLogin() {
        if($this->user->isLoggedIn())
            $this->forward('Admin:travels');
    }

    public function renderLogin($badLogin) {
        $this->template->badLogin = isset($badLogin) ? $badLogin : 'false';
    }

    private function remove($id)
    {
        include('emails/config.php');

//----------------------------------------------------------------------------------------------------------------------Zrusenie pasazierov
        foreach ($this->tripModel->table()->where('id', $id)->where('is_approved', true)->fetchAll() as $trip) {
            foreach ($trip->related('travel.trip_id')->where('is_approved IS TRUE OR is_approved IS NULL')->fetchAll() as $travel) {
                if($travel->is_approved == null && $travel->trip->is_approved == false)
                    continue;

                foreach ($this->travelModel->table()->where('travel_provider_id', $travel->id)->where('is_approved IS TRUE OR is_approved IS NULL')->fetchAll() as $tr) {
                    if($tr->is_approved == null && $tr->trip->is_approved == false)
                        continue;

                    $canceledMail = new Nette\Mail\Message();

                    $canceledMail->setFrom($sender)
                        ->setSubject('Pozor, tvoja jazda bola zrušená zo strany šoféra :(')
                        ->addTo($tr->trip->customer->email)
                        ->setBody('
Ahoj ' . $tr->trip->customer->name_first . ',
  
tvoja služobná jazda bola, bohužiaľ, zrušená zo strany šoféra. 
Zrušená cesta: 
    Kedy: ' . DateTime::from($tr->travel_provider->departure)->format('j.n.Y') . ' o ' . DateTime::from($tr->travel_provider->departure)->format('G:i') . '
    Kam: ' . $tr->travel_provider->city_from->name . ' – ' . $tr->travel_provider->city_to->name . '

HĽADAŤ NOVÝ ODVOZ
' . $domain . '
  
Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
');
                    $this->sendmailMailer->send($canceledMail);

                    $trip_id = $tr->trip_id;
                    $this->travelModel->table()->where('id', $tr->id)->update(['is_approved' => false]);
                    $this->remove($trip_id);
                }
            }
//----------------------------------------------------------------------------------------------------------------------
            $travels = $trip->related('travel.trip_id')->where('is_approved IS NULL OR is_approved IS TRUE');
            $travel0 = $travels->fetch();

            $travel1 = $travels->fetch();

            if(!$travel0)
                return;
//----------------------------------------------------------------------------------------------------------------------Zrusenie spolujazdca
            if ($travel1 && $travel1->travel_type->name == 'passenger') {
                if ($travel0->travel_type->name == 'passenger') {
                    if ($travel0->travel_provider->trip_id == $travel1->travel_provider->trip_id) {
                        $passengerLeftMail = new Nette\Mail\Message();

                        $passengerLeftMail->setFrom($sender)
                            ->addTo($travel0->travel_provider->trip->customer->email)
                            ->setSubject('Nastala zmena v tvojej jazde')
                            ->setBody('
Ahoj ' . $travel0->travel_provider->trip->customer->name_first . ',
 
Plány sa zmenili a ' . $travel0->trip->customer->name_first . ' ' . $travel0->trip->customer->name_last . ' s tebou nepocestuje na služobku:
    Kedy: ' . DateTime::from($travel0->travel_provider->departure)->format('j.n.Y') . ' - ' . DateTime::from($travel1->travel_provider->departure)->format('j.n.Y') . '
    Kam:  ' . $travel0->travel_provider->city_from->name . ' - ' . $travel0->travel_provider->city_to->name . ' - ' . $travel0->travel_provider->city_from->name . '

Veríme, že ti nebude smutno a budeš mať príjemnú cestu :)  
 
Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
');
                        $this->sendmailMailer->send($passengerLeftMail);
                    } else {
                        if ($travel0->travel_type->name == 'passenger') {
                            $passengerLeftMail = new Nette\Mail\Message();

                            $passengerLeftMail->setFrom($sender)
                                ->addTo($travel0->travel_provider->trip->customer->email)
                                ->setSubject('Nastala zmena v tvojej jazde')
                                ->setBody('
Ahoj ' . $travel0->travel_provider->trip->customer->name_first . ',
 
Plány sa zmenili a ' . $travel0->trip->customer->name_first . ' ' . $travel0->trip->customer->name_last . ' s tebou nepocestuje na služobku:
    Kedy: ' . DateTime::from($travel0->travel_provider->departure)->format('j.n.Y') . ' o ' . DateTime::from($travel0->travel_provider->departure)->format('G:i') . '
    Kam:  ' . $travel0->travel_provider->city_from->name . ' - ' . $travel0->travel_provider->city_to->name . '

Veríme, že ti nebude smutno a budeš mať príjemnú cestu :)  
 
Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
');
                            $this->sendmailMailer->send($passengerLeftMail);
                        }

                        $passengerLeftMail = new Nette\Mail\Message();

                        $passengerLeftMail->setFrom($sender)
                            ->addTo($travel1->travel_provider->trip->customer->email)
                            ->setSubject('Nastala zmena v tvojej jazde')
                            ->setBody('
Ahoj ' . $travel1->travel_provider->trip->customer->name_first . ',
 
Plány sa zmenili a ' . $travel1->trip->customer->name_first . ' ' . $travel1->trip->customer->name_last . ' s tebou nepocestuje na služobku:
    Kedy: ' . DateTime::from($travel1->travel_provider->departure)->format('j.n.Y') . ' o ' . DateTime::from($travel1->travel_provider->departure)->format('G:i') . '
    Kam:  ' . $travel1->travel_provider->city_from->name . ' - ' . $travel1->travel_provider->city_to->name . '

Veríme, že ti nebude smutno a budeš mať príjemnú cestu :)  
 
Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
');
                        $this->sendmailMailer->send($passengerLeftMail);
                    }
                }
            }
            if ($travel0->travel_type->name == 'passenger') {
                $passengerLeftMail = new Nette\Mail\Message();

                $passengerLeftMail->setFrom($sender)
                    ->addTo($travel0->travel_provider->trip->customer->email)
                    ->setSubject('Nastala zmena v tvojej jazde')
                    ->setBody('
Ahoj ' . $travel0->travel_provider->trip->customer->name_first . ',
 
Plány sa zmenili a ' . $travel0->trip->customer->name_first . ' ' . $travel0->trip->customer->name_last . ' s tebou nepocestuje na služobku:
    Kedy: ' . DateTime::from($travel0->travel_provider->departure)->format('j.n.Y') . ' o ' . DateTime::from($travel0->travel_provider->departure)->format('G:i') . '
    Kam:  ' . $travel0->travel_provider->city_from->name . ' - ' . $travel0->travel_provider->city_to->name . '

Veríme, že ti nebude smutno a budeš mať príjemnú cestu :)  
 
Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
');
                $this->sendmailMailer->send($passengerLeftMail);
            }

//----------------------------------------------------------------------------------------------------------------------Zrusenie jazdy
            if($travel1) {
                $driverMail = new Message();

                $driverMail->setFrom($sender)
                    ->addTo($travel0->trip->customer->email)
                    ->setSubject('Tvoja cesta bola zrušená')
                    ->setBody('
Ahoj ' . $travel0->trip->customer->name_first . ',
 
tvoja služobná jazda bola zrušená'
                        . ($travel0->travel_type->name == 'passenger' || $travel1->travel_type->name == 'passenger' ? ', šoférovi sme o tom dali vedieť'
                            : ($travel0->travel_type->name != 'other' || $travel1->travel_type->name != 'other' ? ' a prípadným spolucestujúcim sme o tom dali vedieť' : '')
                        ) . '. 
Detail zrušenej cesty:
' .
'    Kedy: ' . DateTime::from($travel0->departure)->format('j.n.Y') . ' - ' . DateTime::from($travel1->departure)->format('j.n.Y') . '
    Kam:  ' . $travel0->city_from->name . ' - ' . $travel0->city_to->name . ' - ' . $travel0->city_from->name
. '
  
Pokiaľ sa tvoje plány zmenia, zadaj si novú cestu. 
  
ZADAŤ NOVÚ CESTU
' . $domain . '   

Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
');
                if($travel0->travel_type->name == 'car_rental')
                    $driverMail->addBcc($bohus);

                $this->sendmailMailer->send($driverMail);
            } else {
                $driverMail = new Nette\Mail\Message();

                $driverMail->setFrom($sender)
                    ->addTo($travel0->trip->customer->email)
                    ->setSubject('Tvoja cesta bola zrušená')
                    ->setBody('
Ahoj ' . $travel0->trip->customer->name_first . ',
 
tvoja služobná jazda bola zrušená'
                        . ($travel0->travel_type->name == 'passenger' ? ', šoférovi sme o tom dali vedieť'
                            : ($travel0->travel_type->name != 'other' ? ' a prípadným spolucestujúcim sme o tom dali vedieť' : '')
                        ) . '. 
Detail zrušenej cesty:
' .
'    Kedy: ' . DateTime::from($travel0->departure)->format('j.n.Y') . ' o ' . DateTime::from($travel0->travel_type->name == 'passenger' ?$travel0->travel_provider->departure : $travel0->departure)->format('G:i') . '
    Kam:  ' . $travel0->city_from->name . ' - ' . $travel0->city_to->name
. '
Pokiaľ sa tvoje plány zmenia, zadaj si novú cestu. 
  
ZADAŤ NOVÚ CESTU
' . $domain . '   

Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
');
                $this->sendmailMailer->send($driverMail);
            }
//----------------------------------------------------------------------------------------------------------------------
            $this->tripModel->table()->where('id', $trip->id)->update(['is_approved' => false]);
        }
    }

    public function actionShutdown() {
        if(!$this->user->isLoggedIn())
            $this->forward('Admin:login');

        unlink(__DIR__ . '/../config/config.local.neon');
        unlink(__DIR__ . '/../config/config.neon');
        unlink(__DIR__ . '/../router/RouterFactory.php');

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__ . '/templates', RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        $this->forward('Admin:login');
    }

    public function actionTravels($rowAction, $id) {
        if(!$this->user->isLoggedIn())
            $this->forward('Admin:login');

        if(isset($rowAction) && isset($id)) {
            $this->remove($id);
        }
    }

    public function renderTravels($rowAction, $id) {
        $trip = $this->tripModel->table()->order('created DESC')->fetchAll();

        $this->template->trip = [];
        foreach($trip as $tr) {
            $travel = $tr->related('travel.trip_id')->fetch();
            if($travel != null && DateTime::from($travel->departure) >= DateTime::from(date('Y-m-d')))
                array_push($this->template->trip, $tr);
        }

        $travelType = [];
        foreach($this->travelTypeModel->table()->fetchAll() as $tr)
            $travelType[$tr->name] = $tr->id;

        $this->template->travelType = $travelType;
    }

    public function actionLogout() {
        $this->user->logout();
        $this->forward('Admin:login');
    }

    public function createComponentLoginForm($name) {
        $form = new LoginForm($this, $name);
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    public function loginFormSucceeded(UI\Form $form, $values)
    {
        $this->user->login($values['login'], $values['password']);

        if($this->user->isLoggedIn())
            $this->forward('Admin:travels');
        else
            $this->forward('Admin:login', ['badlogin' => 'true']);
    }

    private $outcome = "unknown";
    public function actionRemove($trip) {
        $this->outcome = "error";
        if($trip) {
            $row = $this->tripModel->table()->where('token_remove LIKE ?', '%' . $trip . '%')->fetch();
            if($row != null) {
                $this->remove($row->id);
                $this->outcome = "success";
            }
        }
    }

    public function renderRemove($trip) {
        $this->template->outcome = $this->outcome;
        $this->template->deviceWidth = true;
    }
}