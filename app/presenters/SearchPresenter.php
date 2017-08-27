<?php

namespace App\Presenters;

use Nette,
    App\Models,
    Nette\Application\UI,
    Nette\Utils\DateTime;
use Tracy\Debugger, Tracy\OutputDebugger;


class SearchPresenter extends BasePresenter
{
    /**
     * @inject
     * @var Models\CityModel
     */
    public $cityModel;

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

    public $cityFrom = null, $cityTo = null, $tripType = null;

    public function __construct()
    {
        parent::__construct();

        $this->travelSelector0 = new \Components\TravelSelector('travelSelector_0', date('Y-m-d'));
        $this->travelSelector0->onDateChange[] = [$this, 'travelSelectorDateChange'];
        $this->travelSelector1 = new \Components\TravelSelector('travelSelector_1', date('Y-m-d'));
        $this->travelSelector1->onDateChange[] = [$this, 'travelSelectorDateChange'];
    }

    public function renderRequest($cityFrom, $cityTo, $tripType) {
        if($cityFrom !== null && $cityTo !== null && $tripType !== null) {
            $this->cityFrom = $this->template->cityFrom = $cityFrom;
            $this->cityTo = $this->template->cityTo = $cityTo;
            $this->tripType = $this->template->tripType = $tripType;
        }
    }

    public function renderShow($cityFrom, $cityTo, $tripType) {
        $this->template->cityFrom = $cityFrom;
        $this->template->cityTo = $cityTo;
        $this->template->tripType = $tripType;
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

    public function travelSelectorDateChange($caller)
    {
        $ts0 = $this->travelSelector0;
        $ts1 = $this->travelSelector1;

        if($caller == $ts0 && $ts1->getDate() < $ts0->getDate())
            $ts1->setDate($ts0->getDate());
        else if($caller == $ts1 && $ts1->getDate() < $ts0->getDate())
            $ts0->setDate($ts1->getDate());
    }
}
