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

    public function renderRequest($cityFrom, $cityTo, $tripType) {
        $cities = [];
        foreach($this->cityModel->table() as $val)
            array_push($cities, $val->name);

        $this->template->cities = $cities;

        if($cityFrom !== null && $cityTo !== null && $tripType !== null) {
            $this->cityFrom = $this->template->cityFrom = $cityFrom;
            $this->cityTo = $this->template->cityTo = $cityTo;
            $this->tripType = $this->template->tripType = $tripType;
        }
    }

    public function actionShow($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1) {
        $this->cityFrom = $cityFrom;
        $this->cityTo = $cityTo;
        $this->tripType = $tripType;
        error_log("ACTION");

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
        $this->template->cityFrom = $cityFrom;
        $this->template->cityTo = $cityTo;
        $this->template->tripType = $tripType;
        error_log("RENDER");

        $hasRental = $this->cityModel->table()->where('name', $cityFrom)->fetchField('has_rental');
        if(!$hasRental)
            $this->travelSelector0->onlyPoolCar = 1;

        $this->travelSelector0->override = $this->travelSelector1->override = false;

        $this->template->travelSelector0 = $this->travelSelector0;
        $this->template->travelSelector1 = $this->travelSelector1;
        $this->template->allowedInput = $this->travelSelector0->currentTravelType != null && $this->travelSelector1->currentTravelType != null ? true : false;
        $this->template->headerTitle = "Vyhľadané spojenia";
    }

    public function renderSummary($cityFrom, $cityTo, $tripType, $departure0, $travelType0, $travelProvider0, $departure1, $travelType1, $travelProvider1)
    {
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
        $cityFromId = $this->cityModel->table()->where('name', $cityFrom)->fetchField('id');
        $cityToId = $this->cityModel->table()->where('name', $cityTo)->fetchField('id');

        $email = $email . '@o2.sk';

        $customer = $this->customerModel->table()->where('email', $email)->fetch();

        if ($customer === false) {
            $customer = $this->customerModel->table()->insert([
                'email' => $email,
                'phone' => (strlen($phone) < 10 ? null : $phone)
            ]);

            $mailConfirm = new Message();
            $mailConfirm->setFrom('gallo@xgallom.sk')
                ->addTo($email)
                ->setSubject('Potvrdenie účtu O2 Dobrá jazda')
                ->setBody('<a href="o2-carpool.xgallom.sk/web/mail/confirm?customer=' . $customer->id . '"');

            $this->sendmailMailer->send($mailConfirm);
//            Debugger::dump($mailConfirm->generateMessage());
        }

        $trip = $this->tripModel->table()->insert([
            'customer_id' => $customer->id
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
                'spots' => $spots1 == null ? 0 : intval($spots1)
            ]);
        }

        $this->forward('Search:request', [
            $cityFrom,
            $cityTo,
            $tripType
        ]);
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
