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

    /**
     * @var bool
     * @persistent
     */
    public $mobile = false;

    public function __construct()
    {
        parent::__construct();

        $this->travelSelector0 = new \Components\TravelSelector('travelSelector_0', date('Y-m-d'));
        $this->travelSelector0->onDataChange[] = [$this, 'travelSelectorDataChange'];

        $this->travelSelector1 = new \Components\TravelSelector('travelSelector_1', date('Y-m-d'));
        $this->travelSelector1->onDataChange[] = [$this, 'travelSelectorDataChange'];
        $this->travelSelector1->onlyPoolCar = 1;
    }

    public function renderList($mobile) {
        $this->mobile = $this->template->mobile = $mobile;

        $table = $this->travelModel->table()
	        ->where('trip.customer.is_confirmed', true)
	        ->where('departure > ?', date('Y-m-d'))
            ->order('departure');

        $dbData = $table->fetchAll();

        $this->template->dbData = [];
        $n = 0;
        foreach($dbData as $val) {
            $approved = false;
            if($val->is_approved == null)
                $approved = $val->trip->is_approved;
            else
                $approved = $val->is_approved;

            if($approved) {
                if ($n++ > 40)
                    break;

                $passengers = $this->travelModel->table()
                    ->where('trip.is_approved', true)
                    ->where('trip.customer.is_confirmed', true)
                    ->where('travel_provider_id', $val->id);
                $count = $passengers->count();

                array_push($this->template->dbData, [
                    'row' => $val,
                    'passengers' => $count > 0 ? $passengers->fetchAll() : null,
                    'datestr' => DateTime::from($val->departure)->format('j.n.Y G:i'),
                    'date' => DateTime::from($val->departure)->format('Y-m-d'),
                    'time' => DateTime::from($val->departure)->format('H:i'),
                    'spots' => $val->spots - $count
                ]);
            }
        }

        $this->template->dbDate = $dbData;
        $this->template->error = count($dbData) > 0 ? false : true;
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

    public function actionShow($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1, $mobile) {
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

                $firstDate = DateTime::from($departure0)->modify($mobile ? "-1 days" : "-3 days");
                $this->travelSelector0->firstDate = $firstDate < $this->travelSelector0->minimumDate ? $this->travelSelector0->minimumDate : $firstDate->format("Y-m-d");
            }
            if ($travelType0 != null)
                $this->travelSelector0->currentTravelType = $travelType0;
            if ($travelProvider0 != null)
                $this->travelSelector0->currentTravelProvider = $travelProvider0;

            if($departure1 != null) {
                $this->travelSelector1->currentDate = $departure1;

                $firstDate = DateTime::from($departure1)->modify($mobile ? "-1 days" : "-3 days");
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

    public function renderShow($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1, $mobile) {
//        $this->template->deviceWidth = '850px';
        $this->mobile = $this->template->mobile = $mobile;
        $this->travelSelector0->mobile = $this->travelSelector1->mobile = $mobile == '1' ? true : false;

        $this->template->cityFrom = $cityFrom;
        $this->template->cityTo = $cityTo;
        $this->template->tripType = $tripType;
        $this->template->departure0 = $departure0;
        $this->template->travelType0 = $travelType0;
        $this->template->travelProvider0 = $travelProvider0;
        $this->template->departure1 = $departure1;
        $this->template->travelType1 = $travelType1;
        $this->template->travelProvider1 = $travelProvider1;

        $hasRental = $this->cityModel->table()->where('name', $cityFrom)->fetchField('has_rental');
        if(!$hasRental)
            $this->travelSelector0->onlyPoolCar = 1;

        $this->travelSelector0->override = $this->travelSelector1->override = false;

        $this->template->travelSelector0 = $this->travelSelector0;
        $this->template->travelSelector1 = $this->travelSelector1;
        $this->template->allowedInput = $this->travelSelector0->currentTravelType != null && ($tripType != "true" || $this->travelSelector1->currentTravelType != null) ? true : false;
        $this->template->headerTitle = "Vyhľadané spojenia";
    }

    public function actionSummary($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1, $error, $mobile)
    {
        // TODO: implement weekends
        $date = DateTime::from(Date('Y-m-d'))->modify('+1 day');
        $rental = DateTime::from($departure0);

        if($travelType0 == "car_rental" && ($rental < $date || (intval(date('G')) >= 15 && $rental < $date->modify('+1 day'))))
            $this->forward('Search:notify');
    }

    public function renderSummary($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1, $error, $mobile)
    {
        $this->mobile = $mobile;
        $this->template->mobile = $this->mobile;
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

        $this->template->error = $error;
    }

    public function actionSubmit($cityFrom, $cityTo, $tripType,
                                 $departure0, $travelType0, $travelProvider0,
                                 $departure1, $travelType1, $travelProvider1,
                                 $departureTime0, $spots0, $departureTime1, $spots1,
                                 $email, $phone, $supervisor)
    {
        $error = false;
        if(empty($email) || !filter_var($email . '@o2.sk', FILTER_VALIDATE_EMAIL))
            $error = "email";

        if($travelType0 == 'car_rental') {
            if (empty($supervisor) || !filter_var($supervisor . '@o2.sk', FILTER_VALIDATE_EMAIL))
                $error = "supervisor";
        }
        else if($travelType0 == 'car_personal' || $travelType0 == 'car_company')
            if(intval($spots0) < 1 || intval($spots0) > 4)
                $error = "spots";

        if($tripType == 'true')
            if($travelType1 == 'car_personal' || $travelType1 == 'car_company')
                if(intval($spots1) < 1 || intval($spots1) > 4)
                    $error = "spots";

        if($error != false)
            $this->forward('Search:processed', [
                'error' => $error
            ]);

        $cityFromId = $this->cityModel->table()->where('name', $cityFrom)->fetchField('id');
        $cityToId = $this->cityModel->table()->where('name', $cityTo)->fetchField('id');

        $superName = explode('.', $supervisor, 2);
        $superName = ucfirst($superName[0]);

        $email = strtolower(preg_replace('/[\x00-\x1F\x7F]/u', '', $email));
        $supervisor = strtolower(preg_replace('/[\x00-\x1F\x7F]/u', '', $supervisor));
        setcookie('last_mail', $email, time()+60*60*24*30);
        setcookie('last_phone', $phone, time()+60*60*24*30);
        setcookie('last_super', $supervisor, time()+60*60*24*30);

        $name = explode('.', $email, 2);

        include('emails/config.php');

        $email = $email . $suffix;
        $supervisor = $supervisor . $suffix;

        $firstName = $name[0];
        $lastName = null;
        if(count($name) >= 2) {
            $lastName = $name[1];
            $lastName = ucfirst($lastName);
        }

        $firstName = ucfirst($firstName);

        $customer = $this->customerModel->table()->where('email LIKE ?', '%' . $email . '%')->fetch();

        $created = false;
        if ($customer === false) {
            $created = true;
            $token_confirm = bin2hex(openssl_random_pseudo_bytes(16));

            $customer = $this->customerModel->table()->insert([
                'email' => $email,
                'name_first' => $firstName,
                'name_last' => $lastName,
                'phone' => (strlen($phone) < 10 ? null : $phone),
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

' . $domain . 'mail/confirm?customer=' . $token_confirm . '  

Ďakujeme a tešíme sa, že ti zjednodušujeme cestovanie.

Dobrá jazda
Odvoz na dva kliky

Ak ti prišla táto správa bez toho, aby si zadal svoj email na webe www.dobrajazda.sk, daj nám, prosím, o tom vedieť na dobrajazda@o2.sk.
');
            $this->sendmailMailer->send($mailConfirm);
//----------------------------------------------------------------------------------------------------------------------

        }

        if(strlen($phone) >= 10)
            $this->customerModel->table()->where('id', $customer->id)->update([
                'phone' => $phone
            ]);

//----------------------------------------------------------------------------------------------------------------------

        if($this->travelModel->table()->where('trip.customer.id', $customer->id)->where('travel.is_approved IS TRUE OR (travel.is_approved IS NULL AND trip.is_approved IS TRUE)')->where('to_char(departure, \'YYYY-MM-DD\') = \'' . $departure0 . '\'')->count() > 0
        || ($tripType == 'true' && $this->travelModel->table()->where('trip.customer.id', $customer->id)->where('travel.is_approved IS TRUE OR (travel.is_approved IS NULL AND trip.is_approved IS TRUE)')->where('to_char(departure, \'YYYY-MM-DD\') = \'' . $departure1 . '\'')->count() > 0)
        ) {
//            Debugger::dump($this->travelModel->table()->where('trip.customer.id', $customer->id)->where('to_char(departure, \'YYYY-MM-DD\') = \'' . $departure0 . '\'')->fetchAll());
//            Debugger::dump($this->travelModel->table()->where('trip.customer.id', $customer->id)->where('to_char(departure, \'YYYY-MM-DD\') = \'' . $departure1 . '\'')->fetchAll());
            $this->forward('Search:processed', ['error' => 'duplicate']);
        }

        $this->tripModel->db()->beginTransaction();

        $tokenRemove = bin2hex(openssl_random_pseudo_bytes(16));
        $trip = $this->tripModel->table()->insert([
            'customer_id' => $customer->id,
            'token_remove' => $tokenRemove
        ]);

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
                'spots' => $spots1 == null ? 0 : intval($spots1),
            ]);
        }

        $this->tripModel->db()->commit();

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
            try {
                $mailSupervisor = new Message();
                $mailSupervisor->setFrom($sender)
                    ->addTo($supervisor)
                    ->setSubject('Zápožička služobného auta')
                    ->setBody($body);
                $this->sendmailMailer->send($mailSupervisor);
            } catch(AssertionException $e) {
                $this->forward('Search:processed', [
                    'error' => 'email'
                ]);
            }

        }

//----------------------------------------------------------------------------------------------------------------------

        if($created === true)
            $this->forward('Search:processed');

//----------------------------------------------------------------------------------------------------------------------

        include('emails/emails_added.php');

        $this->forward('Search:processed');
    }

    public function renderNotify()
    {
        $this->template->deviceWidth = true;
    }

    public function renderProcessed($error) {
        $this->template->deviceWidth = true;


        if(isset($error)) {
            $this->template->error = true;
            $this->template->errormsg = $error;
        }
        else
            $this->template->error = false;
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

                if($newProvider == null) {
                    if($ts1->currentTravelType == "car_rental")
                        $ts1->currentTravelType = null;

                    // break
                }
                else {
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
            $ts1->currentTravelProvider,
            $this->mobile
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
