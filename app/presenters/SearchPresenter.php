<?php

namespace App\Presenters;

use Nette,
    App\Models,
    Nette\Application\UI,
    Nette\Utils\DateTime,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer;
use Tracy\Debugger, Tracy\OutputDebugger;


class SearchPresenter extends BasePresenter
{
    /**
     * @inject
     * @var Nette\Mail\SendmailMailer
     */
    public $sendmailMailer;

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

    /**
     * @var string
     */
    public $cityFrom = null, $cityTo = null, $tripType = null;

    public function __construct()
    {
        parent::__construct();

        $this->travelSelector0 = new \Components\TravelSelector('travelSelector_0', date('Y-m-d'));
        $this->travelSelector0->onDataChange[] = [$this, 'travelSelectorDataChange'];

        $this->travelSelector1 = new \Components\TravelSelector('travelSelector_1', date('Y-m-d'));
        $this->travelSelector1->onDataChange[] = [$this, 'travelSelectorDataChange'];
        $this->travelSelector1->onlyPoolCar = 1;
    }

    public function renderList() {
        $table = $this->travelModel->table()->where('travel_type.is_provider', true);
        $table->where('travel_type.is_provider', true);
        $table->where('trip.customer.is_confirmed', true);
        $table->order('departure')->limit(10);

        $dbData = $table->fetchAll();

        $this->template->dbData = [];
        foreach($dbData as $val) {
            array_push($this->template->dbData, [
                'row' => $val,
                'datestr' => DateTime::from($val->departure)->format('j.n.Y G:i'),
                'date' => DateTime::from($val->departure)->format('Y-m-d'),
                'time' => DateTime::from($val->departure)->format('H:i'),
                'spots' => $val->spots - $this->travelModel->table()
                        ->where('trip.is_approved', true)->where('trip.customer.is_confirmed', true)->where('travel_provider_id', $val->id)
                        ->count()
            ]);
        }

        $this->template->dbDate = $dbData;
    }

    public function renderRequest($cityFrom, $cityTo, $tripType) {
        $cities = [];
        foreach($this->cityModel->table() as $val)
            array_push($cities, $val->name);

        $this->template->cities = $cities;

        if($cityFrom !== null && $cityTo !== null && $tripType !== null) {
            $this->cityFrom = $this->template->cityFrom = $cityFrom;
            $this->cityTo = $this->template->cityTo = $cityTo;
            $this->tripType = $this->template->tripType = $tripType ? 'true' : 'false';
        }
    }

    public function actionShow($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1) {
        setcookie('last_from', $cityFrom, time()+60*60*24*30);
        setcookie('last_to', $cityTo, time()+60*60*24*30);
        setcookie('last_triptype', $tripType, time()+60*60*24*30);

        $this->cityFrom = $cityFrom;
        $this->cityTo = $cityTo;
        $this->tripType = $tripType;

        if($tripType == 'false') {
            $this->travelSelector0->onlyPoolCar = 1;
        }

        if($this->travelSelector0->override || $this->travelSelector1->override) {
            if($departure0 != null) {
                $this->travelSelector0->currentDate = $departure0;

                $firstDate = DateTime::from($departure0)->modify("-3 days");
                $this->travelSelector0->firstDate = $firstDate < $this->travelSelector0->minimumDate ? $this->travelSelector0->minimumDate : $firstDate->format("Y-m-d");
            }
            if ($travelType0 != null)
                $this->travelSelector0->currentTravelType = $travelType0;
            if ($travelProvider0 != null)
                $this->travelSelector0->currentTravelProvider = $travelProvider0;

            if($departure1 != null) {
                $this->travelSelector1->currentDate = $departure1;

                $firstDate = DateTime::from($departure1)->modify("-3 days");
                $this->travelSelector1->firstDate = $firstDate < $this->travelSelector1->minimumDate ? $this->travelSelector1->minimumDate : $firstDate->format("Y-m-d");
            }
            if($travelType1 != null)
                $this->travelSelector1->currentTravelType = $travelType1;
            if($travelProvider1 != null)
                $this->travelSelector1->currentTravelProvider = $travelProvider1;
        }

        if($this->travelSelector1->currentTravelType == "car_rental")
            $this->travelSelector1->onlyPoolCar = 2;
        else
            $this->travelSelector1->onlyPoolCar = 1;
    }

    public function renderShow($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1) {
//        $this->template->deviceWidth = '850px';
        $this->template->cityFrom = $cityFrom;
        $this->template->cityTo = $cityTo;
        $this->template->tripType = $tripType;

        $hasRental = $this->cityModel->table()->where('name', $cityFrom)->fetchField('has_rental');
        if(!$hasRental)
            $this->travelSelector0->onlyPoolCar = 1;

        $this->travelSelector0->override = $this->travelSelector1->override = false;

        $this->template->travelSelector0 = $this->travelSelector0;
        $this->template->travelSelector1 = $this->travelSelector1;
        $this->template->allowedInput = $this->travelSelector0->currentTravelType != null && $this->travelSelector1->currentTravelType != null ? true : false;
        $this->template->headerTitle = "Vyhľadané spojenia";
    }

    public function actionSummary($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1)
    {
        // TODO: implement weekends
        $date = DateTime::from(Date('Y-m-d'))->modify('+1 day');
        $rental = DateTime::from($departure0);

        if($travelType0 == "car_rental" && ($rental < $date || (intval(date('G')) >= 15 && $rental < $date->modify('+1 day'))))
            $this->forward('Search:notify');
    }

    public function renderSummary($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1)
    {
        $this->template->lastMail = isset($_COOKIE['last_mail']) ? $_COOKIE['last_mail'] : '';
        $this->template->lastPhone = isset($_COOKIE['last_phone']) ? $_COOKIE['last_phone'] : '09';
        $this->template->lastSuper = isset($_COOKIE['last_super']) ? $_COOKIE['last_super'] : '';

        $this->template->cityFrom = $cityFrom;
        $this->template->cityTo = $cityTo;
        $this->template->tripType = $tripType;

        $this->template->departure0 = $departure0;
        $this->template->travelType0 = $travelType0;
        $this->template->travelProvider0 = $travelProvider0;
        $this->template->departure1 = $departure1;
        $this->template->travelType1 = $travelType1;
        $this->template->travelProvider1 = $travelProvider1;
    }

    public function actionSubmit($cityFrom, $cityTo, $tripType,
                                 $departure0, $travelType0, $travelProvider0,
                                 $departure1, $travelType1, $travelProvider1,
                                 $departureTime0, $spots0, $departureTime1, $spots1,
                                 $email, $phone, $supervisor)
    {
        setcookie('last_mail', $email, time()+60*60*24*30);
        setcookie('last_phone', $phone, time()+60*60*24*30);
        setcookie('last_super', $supervisor, time()+60*60*24*30);

        $cityFromId = $this->cityModel->table()->where('name', $cityFrom)->fetchField('id');
        $cityToId = $this->cityModel->table()->where('name', $cityTo)->fetchField('id');

        $superName = explode('.', $supervisor, 2);
        $superName = ucfirst($superName[0]);

        $name = explode('.', $email, 2);
        $email = $email . '@gmail.com';
        $supervisor = $supervisor . '@gmail.com';
        $bohus = 'minion1696@gmail.com';
        $sender = 'gallo@xgallom.sk';
        $domain = 'http://o2-carpool.xgallom.sk/web';

        $firstName = $name[0];
        $lastName = null;
        if(count($name) >= 2) {
            $lastName = $name[1];
            $lastName = ucfirst($lastName);
        }

        $firstName = ucfirst($firstName);

        $customer = $this->customerModel->table()->where('email', $email)->fetch();

        if ($customer === false) {
            $customer = $this->customerModel->table()->insert([
                'email' => $email,
                'name_first' => $firstName,
                'name_last' => $lastName,
                'phone' => (strlen($phone) < 10 ? null : $phone)
            ]);

//            Debugger::dump($mailConfirm->generateMessage());
        }

        if(strlen($phone) >= 10)
            $this->customerModel->table()->where('id', $customer->id)->update([
                'phone' => $phone
            ]);

        if($customer->is_confirmed === false) {
            $token_confirm = bin2hex(openssl_random_pseudo_bytes(16));

            $this->customerModel->table()->where('id', $customer->id)->update([
                'token_confirm' => $token_confirm
            ]);

//---------------------------------------------------------------------------------------------------------------------- Konfirmacny mail
            $mailConfirm = new Message();
            $mailConfirm->setFrom($sender)
                ->addTo($email)
                ->setSubject('Vitaj v Dobrej jazde')
                ->setBody('Ahoj ' . $firstName . ',

aby sme si boli istí, že si to naozaj ty, prosím, potvrď zadanie tvojej služobnej cesty cez dobrajazda.sk.

Stačí, keď sa identifikuješ iba raz. Následne už od teba nebudeme požadovať nič navyše. :)

' . $domain . '/mail/confirm?customer=' . $token_confirm . '  

Ďakujeme a tešíme sa, že ti zjednodušujeme cestovanie.

Dobrá jazda
Odvoz na dva kliky

Ak ti prišla táto správa bez toho, aby si zadal svoj email na webe www.dobrajazda.sk, daj nám, prosím, o tom vedieť na dobrajazda@o2.sk.
');
            $this->sendmailMailer->send($mailConfirm);
//----------------------------------------------------------------------------------------------------------------------
        }

        $trip = $this->tripModel->table()->insert([
            'customer_id' => $customer->id
        ]);

//---------------------------------------------------------------------------------------------------------------------- Mail nadriadenemu
        if($travelType0 == 'car_rental') {
            $body = 'Ahoj ' . $superName . ', 

' . $firstName . ' ' . $lastName . ' si potrebuje požičať služobné auto na svoju služobnú cestu 
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' - ' . DateTime::from($departure1)->format('j.n.Y') . '
    Kam: ' . $cityFrom . ' - ' . $cityTo . ' - ' . $cityFrom . ' 

V prípade nesúhlasu so zápožičkou služobného vozidla, prosím, napíš na dobrajazda@o2.sk. 
V opačnom prípade bude cesta považovaná za schválenú.
    
Ďakujeme ti za spoluprácu,  
  
Dobrá jazda';
            $mailSupervisor = new Message();
            $mailSupervisor->setFrom($sender)
                ->addTo($supervisor)
                ->setSubject('Zápožička služobného auta')
                ->setBody($body);
            $this->sendmailMailer->send($mailSupervisor);
        }
//---------------------------------------------------------------------------------------------------------------------- Mail nova jazda
        $body = 'Ahoj ' . $firstName . ',
  
ďakujeme za zadanie tvojej služobnej jazdy:';

        if($travelType0 == 'car_rental') {
            $body .= '
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' - ' . DateTime::from($departure1)->format('j.n.Y') . '
    Kam: ' . $cityFrom . ' - ' . $cityTo . ' - ' . $cityFrom . '

Prosím, spoj sa s Bohušom, ktorý ti odovzdá kľúče a doklady od auta. V prípade, že sa k tebe pridá nejaký kolega, budeme ťa informovať.';
        }
        else if($travelType0 == 'passenger') {
            if($tripType == 'true') {
                $provider0 = $this->travelModel->table()->get($travelProvider0);
                $body .= '
Cesta TAM
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' o ' . DateTime::from($provider0->departure)->format('G:i') . '
    Kam: ' . $cityFrom . ' - ' . $cityTo . '
Šofér: ' . $provider0->trip->customer->name_first . ' ' . $provider0->trip->customer->name_last . ', ' . $provider0->trip->customer->email . ($provider0->trip->customer->phone == null ? '' : ', ' . $provider0->trip->customer->phone);

                if($travelType1 == 'passenger') {
                    $provider1 = $this->travelModel->table()->get($travelProvider1);
                    $body .= '

Cesta SPÄŤ
    Kedy: ' . DateTime::from($departure1)->format('j.n.Y') . ' o ' . DateTime::from($provider1->departure)->format('G:i') . '
    Kam: ' . $cityTo . ' - ' . $cityFrom . '
Šofér: ' . $provider1->trip->customer->name_first . ' ' . $provider1->trip->customer->name_last . ', ' . $provider1->trip->customer->email . ($provider1->trip->customer->phone == null ? '' : ', ' . $provider1->trip->customer->phone) . '
                    
Nezabudni sa spojiť so šoférom a dohodnúť si presné miesto a čas odchodu.';
                }
                else {
                    $body .= '
Cesta SPÄŤ
    Kedy: ' . DateTime::from($departure1)->format('j.n.Y') . ' o ' . $departureTime1 . '
    Kam: ' . $cityFrom . ' - ' . $cityTo . '

Nezabudni sa spojiť so šoférom a dohodnúť si presné miesto a čas odchodu.';
                }
            }
            else {
                $provider = $this->travelModel->table()->get($travelProvider0);
                $body .= '
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' o ' . DateTime::from($provider->departure)->format('G:i') . '
    Kam: ' . $cityFrom . ' - ' . $cityTo . '
Šofér: ' . $provider->trip->customer->name_first . ' ' . $provider->trip->customer->name_last . ', ' . $provider->trip->customer->email . ($provider->trip->customer->phone == null ? '' : ', ' . $provider->trip->customer->phone) . '

Nezabudni sa spojiť so šoférom a dohodnúť si presné miesto a čas odchodu.';
            }
        }
        else { // personal, company, other
            if($tripType == 'true') {
                if ($travelType1 == 'passenger') {
                    $provider = $this->travelModel->table()->get($travelProvider1);
                    $body .= '
Cesta TAM
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' o ' . $departureTime0 . '
    Kam: ' . $cityFrom . ' - ' . $cityTo . '

Cesta SPÄŤ
    Kedy: ' . DateTime::from($departure1)->format('j.n.Y') . ' o ' . DateTime::from($provider->departure)->format('G:i') . '
    Kam: ' . $cityTo . ' - ' . $cityFrom . '
Šofér: ' . $provider->trip->customer->name_first . ' ' . $provider->trip->customer->name_last . ', ' . $provider->trip->customer->email . ($provider->trip->customer->phone == null ? '' : ', ' . $provider->trip->customer->phone) . '
';
                }
                else { // personal, company, other
                    $body .= '
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' - ' . DateTime::from($departure1)->format('j.n.Y') . '
    Kam: ' . $cityFrom . ' - ' . $cityTo . ' - ' . $cityFrom;

                    if($travelType0 != 'other' || $travelType1 != 'other')
                        $body .= '

V prípade, že sa k tebe pridá nejaký kolega, budeme ťa informovať.';
                }
            }
            else {
                $body .= '
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' o ' . $departureTime0 . '
    Kam: ' . $cityFrom . ' - ' . $cityTo;

    if($travelType0 != 'other')
        $body .= '

V prípade, že sa k tebe pridá nejaký kolega, budeme ťa informovať.';
            }
        }


        $body .=
'

Pokiaľ by sa tvoja cesta zmenila, napíš nám hneď na dobrajazda@o2.sk.
   
Pohodovú cestu ti želá,  

Dobrá jazda 
 
P.S.: nezabudni si svoju služobku nahodiť aj do dochádzky a prípadné preplatenie nákladov vyúčtovať cez SAP';

        $mailNewTravel = new Message();
        $mailNewTravel->setFrom($sender)
            ->addTo($email)
            ->setSubject('Gratulujeme, máš zadanú novú jazdu')
            ->setBody($body);

        if($travelType0 == 'car_rental')
            $mailNewTravel->addBcc($bohus);

        $this->sendmailMailer->send($mailNewTravel);
//----------------------------------------------------------------------------------------------------------------------

        $this->travelModel->table()->insert([
            'departure' => $departure0 . ' ' . $departureTime0,
            'travel_type_id' => $this->travelTypeModel->table()->where('name', $travelType0)->fetch()->id,
            'travel_provider_id' => $travelProvider0 ? $travelProvider0 : null,
            'trip_id' => $trip->id,
            'city_from_id' => $cityFromId,
            'city_to_id' => $cityToId,
            'spots' => $spots0 == null ? 0 : intval($spots0)
        ]);

        if($tripType == 'true') {
            $this->travelModel->table()->insert([
                'departure' => $departure1 . ' ' . $departureTime1,
                'travel_type_id' => $this->travelTypeModel->table()->where('name', $travelType1)->fetch()->id,
                'travel_provider_id' => $travelProvider1 ? $travelProvider1 : null,
                'trip_id' => $trip->id,
                'city_from_id' => $cityToId,
                'city_to_id' => $cityFromId,
                'spots' => $spots1 == null ? 0 : intval($spots1)
            ]);
        }

//---------------------------------------------------------------------------------------------------------------------- Pridal sa k posadke mail
        if($travelType0 == 'passenger') {
            $provider = $this->travelModel->table()->get($travelProvider0);

            $body = 'Ahoj ' . $provider->trip->customer->name_first . '

k tvojej služobnej jazde 
    Kedy: ' . DateTime::from($departure0)->format('j.n.Y') . ' o ' . DateTime::from($provider->departure)->format('G:i') . ' 
    Kam: ' . $cityFrom . ' - ' . $cityTo . ' 

sa pridal: ' . $firstName . ' ' . $lastName . ', ' . $email . (strlen($phone) >= 10 ? ', ' . $phone : '') . '  

Pre lepšiu koordináciu a pohodovú jazdu ti odporúčame spojiť sa vopred so všetkými spolucestujúcimi. 
  
Pokiaľ by sa tvoja cesta zmenila, napíš nám hneď na dobrajazda@o2.sk.

Pekný zvyšok dňa ti želá,  
  
Dobrá jazda 
';
            $mailAdded = new Message();
            $mailAdded->setFrom($sender)
                ->addTo($provider->trip->customer->email)
                ->setSubject('Gratulujeme, máš nového spolujazdca')
                ->setBody($body);
            $this->sendmailMailer->send($mailAdded);
        }
        if($tripType == 'true' && $travelType1 == 'passenger') {
            $provider = $this->travelModel->table()->get($travelProvider1);

            $body = 'Ahoj ' . $provider->trip->customer->name_first . '

k tvojej služobnej jazde 
    Kedy: ' . DateTime::from($departure1)->format('j.n.Y') . ' o ' . DateTime::from($provider->departure)->format('G:i') . ' 
    Kam: ' . $cityTo . ' - ' . $cityFrom . ' 

sa pridal: ' . $firstName . ' ' . $lastName . ', ' . $email . (strlen($phone) >= 10 ? ', ' . $phone : '') . '  

Pre lepšiu koordináciu a pohodovú jazdu ti odporúčame spojiť sa vopred so všetkými spolucestujúcimi. 
  
Pokiaľ by sa tvoja cesta zmenila, napíš nám hneď na dobrajazda@o2.sk.

Pekný zvyšok dňa ti želá,
  
Dobrá jazda 
';
            $mailAdded = new Message();
            $mailAdded->setFrom($sender)
                ->addTo($provider->trip->customer->email)
                ->setSubject('Gratulujeme, máš nového spolujazdca')
                ->setBody($body);
            $this->sendmailMailer->send($mailAdded);
        }
//----------------------------------------------------------------------------------------------------------------------

        $this->forward('Search:processed');


        /*
        ->setHtmlBody('
<head>
<style>
.bg {
background-color: #2d2e35;
border-radius: 8px;
padding: 45px 35px;
}
.fg {
width: 700px;
margin: auto;
}
*, p, h2, h4 {
text-align: justify;
color: #d8d6d9;
}
a {
text-decoration: none;
color: #5495d3;
}
a:hover {
color: #2a81d3;
}

.button {
display: block;
margin: auto;
text-align: center;
}
</style>
</head>
<body>
<div class="bg"><div class="fg"><h2>Ahoj ' . $name . ',</h2>
<p>aby sme si boli istí, že si to naozaj ty, prosím, potvrď zadanie tvojej služobnej cesty cez dobrajazda.sk.<br>
Stačí, keď sa identifikuješ iba raz. Následne už od teba nebudeme požadovať nič navyše. :)</p>
<h3><a class="button" href="x5fkyk3yf2.xgallom.sk/web/mail/confirm?customer=' . $token_confirm . '">Áno, som to ja</a></h3>
<p>Ďakujeme a tešíme sa, že ti zjednodušujeme cestovanie.</p>
<h4 style="margin-bottom: 0; -webkit-margin-after: 0;">Dobrá jazda</h4><i>
Odvoz na dva kliky
<p style="text-align: center">Ak ti prišla táto správa bez toho, aby si zadal svoj email na webe www.dobrajazda.sk, daj nám, prosím, o tom vedieť na dobrajazda@o2.sk.</p></i></div></div>
</body>
');*/
    }

    public function renderNotify()
    {

    }

    /**
     * Toto je tovaren na komponenty. Je to vzor Factory a vsetko co potrebujeme je prefixnut
     * nazov funkcie "createComponentXXX" kde XXX je nazov komponenty ktorym ju volame v sablonach presenteru
     *
     * @param $name string
     * @return \Components\TravelFinder
     */
    public function createComponentTravelFinder($name)
    {
        $cities = [];
        foreach($this->cityModel->table() as $val)
            array_push($cities, $val->name);

        $form = new \Components\TravelFinder($this, $name, $cities,
            $this->cityFrom !== null && $this->cityTo !== null && $this->tripType !== null ?
            [
                'city_from' => $this->cityFrom,
                'city_to' => $this->cityTo,
                'trip_type' => $this->tripType
            ] : null
        );

        $form->onSuccess[] = [$this, 'travelFinderSucceeded'];
        return $form;
    }

    public function travelFinderSucceeded(UI\Form $form, $values)
    {
        $this->redirect('Search:show',
            [
                $values['city_from'],
                $values['city_to'],
                $values['trip_type']
            ]
        );
    }

    /**
     * @var \Components\TravelSelector
     */
    private $travelSelector0 = null;
    public function createComponentTravelSelector0()
    {
        $dbModel = new Models\DbModel;
        $dbModel->cityModel = $this->cityModel;
        $dbModel->travelModel = $this->travelModel;
        $dbModel->travelTypeModel = $this->travelTypeModel;
        $this->travelSelector0->dbModel = $dbModel;

        return $this->travelSelector0;
    }

    /**
     * @var \Components\TravelSelector
     */
    private $travelSelector1 = null;
    public function createComponentTravelSelector1()
    {
        $dbModel = new Models\DbModel;
        $dbModel->cityModel = $this->cityModel;
        $dbModel->travelTypeModel = $this->travelTypeModel;
        $dbModel->travelModel = $this->travelModel;
        $this->travelSelector1->dbModel = $dbModel;

        return $this->travelSelector1;
    }

    public function travelSelectorDataChange($caller, $change)
    {
        $ts0 = $this->travelSelector0;
        $ts1 = $this->travelSelector1;

        /** Ak sa zmeni datum a ty si passenger, musi sa resetnut radio aby si nesiel v inom dni */
        if($change == 0 && $caller->currentTravelType == "passenger")
            $caller->currentTravelType = $caller->currentTravelProvider = null;

        /** Check na cestu spat v case */
        if($caller === $ts0 && $ts1->getDate() < $ts0->getDate()) {
            $ts1->setDate($ts0->getDate());

            if($ts1->currentTravelType == "passenger")
                $ts1->currentTravelType = $ts1->currentTravelProvider = null;
        }
        else if($caller === $ts1 && $ts1->getDate() < $ts0->getDate()) {
            $ts0->setDate($ts1->getDate());

            if($ts0->currentTravelType == "passenger")
                $ts0->currentTravelType = $ts0->currentTravelProvider = null;
        }

        /** Ak sa meni typ jazdy tam, a jazda naspet este nie je vybrata, nastavi sa */
        if($ts0->currentTravelType == "passenger")
        {
            /** ako cesta spat s tym istym clovekom */
            if($caller === $ts0 && ($ts1->currentTravelType == null || $ts1->currentTravelType == "car_rental")) {
                $table = $this->travelModel->table();
                $newProvider = $this->travelModel->table()
                    ->where('trip_id', $table->get($ts0->currentTravelProvider)->trip)
                    ->where('id <> ?', $ts0->currentTravelProvider)
                    ->fetch();

                if( DateTime::from($newProvider->departure) >= $ts0->getDate()
                    &&($ts1->currentDate == $ts1->minimumDate->format('Y-m-d')
                    || $ts1->currentDate == DateTime::from($newProvider->departure)->format('Y-m-d'))
                ) {
                    $ts1->currentDate = DateTime::from($newProvider->departure)->format('Y-m-d');
                    $ts1->currentTravelType = "passenger";
                    $ts1->currentTravelProvider = $newProvider->id;
                }
            }
        }
        else if($ts0->currentTravelType == "car_rental") {
            /** Ak tam ides rentalom, musis ist aj naspet */
            $ts1->currentTravelType = $ts0->currentTravelType;
        }
        else {
            /** ako cesta spat s tym istym sposobom*/
            if($caller === $ts0 && $change == 1) {
                if($ts1->currentTravelType == null) {
                    $ts1->currentTravelType = $ts0->currentTravelType;
                    $ts1->currentTravelProvider = $ts0->currentTravelProvider;
                }
            }

            /** Ak tam nejdes rentalom, nemozes ist ani naspet */
            if($ts1->currentTravelType == "car_rental")
                $ts1->currentTravelType = $ts0->currentTravelType;
        }
/*
        $date = DateTime::from($ts0->firstDate);
        $currentDate = DateTime::from($ts0->currentDate);
        if ($currentDate < $date || $currentDate > $date->modify('+6 days'))
            $ts0->setFirstDate($currentDate);

        $date = DateTime::from($ts1->firstDate);
        $currentDate = DateTime::from($ts1->currentDate);
        if ($currentDate < $date || $currentDate > $date->modify('+6 days'))
            $ts1->setFirstDate($currentDate);
*/

        /*
        echo $this->link("Search:show", [
                $this->cityFrom,
                $this->cityTo,
                $this->tripType,
                $ts0->currentDate,
                $ts0->currentTravelType,
                $ts0->currentTravelProvider,
                $ts1->currentDate,
                $ts1->currentTravelType,
                $ts1->currentTravelProvider
            ]);
        */
        $this->redirect("Search:show", [
            $this->cityFrom,
            $this->cityTo,
            $this->tripType,
            $ts0->currentDate,
            $ts0->currentTravelType,
            $ts0->currentTravelProvider,
            $ts1->currentDate,
            $ts1->currentTravelType,
            $ts1->currentTravelProvider
        ]);

        //$this->travelSelector0->override = $this->travelSelector1->override = false;
    }

    public function createComponentTravelInfo0()
    {
        $travelInfo = new \Components\TravelInfo("travelInfo_0", 0);
        $dbModel = new Models\DbModel;
        $dbModel->cityModel = $this->cityModel;
        $dbModel->travelTypeModel = $this->travelTypeModel;
        $dbModel->travelModel = $this->travelModel;
        $travelInfo->dbModel = $dbModel;

        return $travelInfo;
    }
    public function createComponentTravelInfo1()
    {
        $travelInfo = new \Components\TravelInfo("travelInfo_1", 1);
        $dbModel = new Models\DbModel;
        $dbModel->cityModel = $this->cityModel;
        $dbModel->travelTypeModel = $this->travelTypeModel;
        $dbModel->travelModel = $this->travelModel;
        $travelInfo->dbModel = $dbModel;

        return $travelInfo;
    }
}
