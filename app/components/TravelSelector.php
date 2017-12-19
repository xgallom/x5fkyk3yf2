<?php
namespace Components;

use Nette\Application\UI\Control,
    App\Models,
    Nette\Utils\DateTime;
use Tracy\Debugger;
use Tracy\OutputDebugger;

class TravelSelector extends Control
{
    /**
     * @var bool
     */
    public $override = true;

    /**
     * @var bool
     */
    public $mobile = false;

    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $firstDate;
    /**
     * @var string
     */
    public $currentDate;

    /**
     * @var string
     */
    public $currentTravelType;

    /**
     * @var int
     */
    public $currentTravelProvider;

    /**
     * @var Models\DbModel
     */
    public $dbModel;

    /**
     * @var DateTime
     */
    public $minimumDate;

    public $onDataChange = null;

    /**
     * @var int
     */
    public $onlyPoolCar = 0;

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
        $this->template->backDisabled = DateTime::from($this->firstDate) <= $this->minimumDate;
        $this->template->minimumDate = $this->minimumDate;
        $this->template->currentDate = $this->currentDate;
        $this->template->currentTravelType = $this->currentTravelType;
        $this->template->currentTravelProvider = $this->currentTravelProvider;
        $this->template->onlyPoolCar = $this->onlyPoolCar;
        $this->template->mobile = $this->mobile;

        $table = $this->dbModel->cityModel->table();
        $cityFromId = $table->where('name', $cityFrom)->fetch()->id;

        $table = $this->dbModel->cityModel->table();
        $cityToId = $table->where('name', $cityTo)->fetch()->id;

        $table = $this->dbModel->travelModel->table();
        $table->where('city_from_id', $cityFromId);
        $table->where('travel_type.is_provider', true);
        $table->where('trip.customer.is_confirmed', true);
        $table->where('city_to_id', $cityToId);
        $table->where('date(departure)', $this->currentDate);
        $table->order('departure');

        $dbData = $table->fetchAll();

        Debugger::dump($dbData);

        $this->template->dbData = [];
        $n = 0;
        foreach($dbData as $val) {
            $approved = false;
            if($val->is_approved == null)
                $approved = $val->trip->is_approved;
            else
                $approved = $val->is_approved;

            if($approved) {
                if($n++ > 10)
                    break;

                array_push($this->template->dbData, [
                    'row' => $val,
                    'spots' => $val->spots - $this->dbModel->travelModel->table()
                            ->where('trip.is_approved', true)->where('trip.customer.is_confirmed', true)->where('travel_provider_id', $val->id)
                            ->count()
                ]);
            }
        }

        $this->template->collapseShownList = [];
        foreach($this->dbModel->travelTypeModel->table()->where('name != ?', 'passenger')->fetchAll() as $row)
            array_push($this->template->collapseShownList, $row->name);

        $this->generateDates();

        $this->template->render();
    }

    public function handleDateRowChange($amount)
    {
        $date = DateTime::from($this->currentDate);
        $date->modify($amount);
        $this->setDate($date);

        if ($this->onDataChange != null)
            $this->onDataChange($this, 0);


        $this->onDataUpdate();
    }

    public function handleDateChange($val)
    {
        $this->currentDate = $val;
        if ($this->onDataChange != null)
            $this->onDataChange($this, 0);

        $this->setDate(DateTime::from($val));

        $this->onDataUpdate();
    }

    public function handleChangeTravelType($travelType, $travelProvider)
    {
        $this->currentTravelType = $travelType;
        $this->currentTravelProvider = $travelProvider;

        if ($this->onDataChange != null)
            $this->onDataChange($this, 1);

        $this->onDataUpdate();
    }

    public function getDate()
    {
        return DateTime::from($this->currentDate);
    }

    public function setDate($newDate)
    {
        $this->currentDate = $newDate->format('Y-m-d');

        if ($this->onDataChange != null)
            $this->onDataChange($this, 0);

        $date = DateTime::from($this->firstDate);
        $currentDate = $newDate;
        if ($currentDate < $date || $currentDate > $date->modify('+6 days'))
            $this->setFirstDate($currentDate);
    }

    public function getFirstDate()
    {
        return DateTime::from($this->firstDate);
    }

    public function setFirstDate($newFirstDate)
    {
        $this->firstDate = $newFirstDate->format('Y-m-d');
    }

    private function generateDates()
    {
        $this->template->dateList = [];
        $date = DateTime::from($this->firstDate);
        for($i = 0; $i < ($this->mobile ? 4 : 7); $i++) {
            array_push($this->template->dateList, ['val' => $date->format('Y-m-d'), 'date_val' => clone $date, 'day' => $this->dayToSlovak($date->format('D')), 'str' => $date->format('d.m.')]);
            $date->modify('+1 day');
        }
    }
    public function onDataUpdate()
    {
        $this->override = false;
    }

    private function dayToSlovak($date)
    {
        switch($date)
        {
            case 'Mon':
                return 'Pon';
                break;

            case 'Tue':
                return 'Ut';
                break;

            case 'Wed':
                return 'Str';
                break;

            case 'Thu':
                return 'Štv';
                break;

            case 'Fri':
                return 'Pi';
                break;

            case 'Sat':
                return 'Sob';
                break;

            case 'Sun':
                return 'Ned';
                break;

        }
    }
}