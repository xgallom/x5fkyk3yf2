<?php
namespace Components;

use Nette\Application\UI\Control,
    App\Models,
    Nette\Utils\DateTime;
use Tracy\Debugger;

class TravelSelector extends Control
{
    /**
     * @persistent
     * @var string
     */
    public $name;
    /**
     * @persistent
     * @var string
     */
    public $firstDate;
    /**
     * @persistent
     * @var string
     */
    public $currentDate;

    /**
     * @persistent
     * @var string
     */
    public $currentTravelType;

    /**
     * @persistent
     * @var int
     */
    public $currentTravelProvider;

    /**
     * @var Models\DbModel
     */
    public $dbModel;

    /**
     * @persistent
     * @var string
     */
    private $minimumDate;

    public $onDateChange = null, $afterDateChange = null;

    public function __construct($name, $firstDate)
    {
        parent::__construct();

        $this->name = $name;
        $this->minimumDate = DateTime::from($firstDate);
        $this->firstDate = $firstDate;
        $this->currentDate = $firstDate;
    }

    public function render($cityFrom, $cityTo, $tripStep)
    {
        $this->template->setFile(__DIR__ . '/TravelSelector.latte');

        $this->template->name = $this->name;
        $this->template->cityFrom = $cityFrom;
        $this->template->cityTo = $cityTo;
        $this->template->tripStep = $tripStep;
        $this->template->backDisabled = DateTime::from($this->firstDate) == $this->minimumDate;
        $this->template->currentDate = $this->currentDate;
        $this->template->currentTravelType = $this->currentTravelType;
        $this->template->currentTravelProvider = $this->currentTravelProvider;

        $table = $this->dbModel->cityModel->table();
        $cityFromId = $table->where('name', $cityFrom)->fetch()->id;

        $table = $this->dbModel->cityModel->table();
        $cityToId = $table->where('name', $cityTo)->fetch()->id;

        $table = $this->dbModel->travelModel->table();
        $table->where('city_from_id', $cityFromId);
        $table->where('travel_type.is_provider', true);
        $table->where('city_to_id', $cityToId);
        $table->where('date(departure)', $this->currentDate);

        $dbData = $table->fetchAll();

        $this->template->dbData = [];
        foreach($dbData as $val)
            array_push($this->template->dbData, [ 'row' => $val, 'spots' => $val->spots - $this->dbModel->travelModel->table()->where('travel_provider_id', $val->id)->count()]);

        $this->template->collapseShownList = [];
        foreach($this->dbModel->travelTypeModel->table()->where('name != ?', 'passenger')->fetchAll() as $row)
            array_push($this->template->collapseShownList, $row->name);

        $this->generateDates();

        $this->template->render();
    }

    public function handleDateRowChange($amount)
    {
        $date = DateTime::from($this->firstDate);
        $date->modify($amount);

        $this->setFirstDate($date);
    }

    public function handleDateChange($val)
    {
        $this->setDate(DateTime::from($val));
    }

    public function handleChangeTravelType($travelType, $travelProvider)
    {
        $this->currentTravelType = $travelType;
        $this->currentTravelProvider = $travelProvider;
    }

    public function getDate()
    {
        return DateTime::from($this->currentDate);
    }

    public function setDate($newDate)
    {
        $this->currentDate = $newDate->format('Y-m-d');

        if ($this->onDateChange != null)
            $this->onDateChange($this);

        $date = DateTime::from($this->firstDate);
        $currentDate = $newDate;
        if ($currentDate < $date || $currentDate > $date->modify('+6 days'))
            $this->setFirstDate($currentDate);

        if ($this->afterDateChange != null)
            $this->afterDateChange($this);
    }

    public function getFirstDate()
    {
        return DateTime::from($this->firstDate);
    }

    public function setFirstDate($newFirstDate)
    {
        $this->firstDate = $newFirstDate->format('Y-m-d');

        $date = $newFirstDate;
        $currentDate = DateTime::from($this->currentDate);
        if($currentDate < $date || $currentDate > $date->modify('+6 days'))
            $this->setDate($date);
    }

    private function generateDates()
    {
        $this->template->dateList = [];
        $date = DateTime::from($this->firstDate);
        for($i = 0; $i < 7; $i++) {
            array_push($this->template->dateList, ['val' => $date->format('Y-m-d'), 'str' => $date->format('d.m.')]);
            $date->modify('+1 day');
        }
    }
}