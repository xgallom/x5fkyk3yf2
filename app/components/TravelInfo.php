<?php
namespace Components;

use Nette\Application\UI\Control,
    App\Models,
    Nette\Utils\DateTime;
use Tracy\Debugger;
use Tracy\OutputDebugger;

class TravelInfo extends Control
{
    /**
     * @var string
     */
    public $name, $time;

    /**
     * @var int
     */
    public $spots, $id;

    /**
     * @var Models\DbModel
     */
    public $dbModel;

    /**
     * TravelSelector constructor.
     * @param string $name
     */
    public function __construct($name, $id)
    {
        parent::__construct();

        $this->name = $name;
        $this->id = $id;
    }

    public function render($cityFrom, $cityTo, $tripStep, $date, $travelType, $travelProvider)
    {
        $this->template->setFile(__DIR__ . '/TravelInfo.latte');

        $this->template->id = $this->id;

        $this->template->cityFrom = $cityFrom;
        $this->template->cityTo = $cityTo;
        $this->template->tripStep = $tripStep;

        $date = DateTime::from($date);
        $this->template->date = [
            'val' => $date->format('Y-m-d'),
            'date_val' => clone $date,
            'day' => $this->dayToSlovak($date->format('D')),
            'mon' => $this->monToSlovak($date->format('M')),
            'str' => intval($date->format('d'))
        ];

        $this->template->travelType = [
            'val' => $travelType,
            'str' => $this->travelTypeStr($travelType)
        ];

        if($travelType == 'passenger')
        {
            $provider = $this->dbModel->travelModel->table()->get($travelProvider);

            $this->template->time = DateTime::from($provider->departure)->format('G:i');

            $this->template->dbData = $provider;
        }

        $this->template->render();
    }

    private function dayToSlovak($date)
    {
        switch($date)
        {
            case 'Mon':
                return 'Pondelok';
                break;

            case 'Tue':
                return 'Utorok';
                break;

            case 'Wed':
                return 'Streda';
                break;

            case 'Thu':
                return 'Štvrtok';
                break;

            case 'Fri':
                return 'Piatok';
                break;

            case 'Sat':
                return 'Sobota';
                break;

            case 'Sun':
                return 'Nedela';
                break;

        }
    }
    private function monToSlovak($date)
    {
        switch($date)
        {
            case 'Jan':
                return 'Január';
                break;

            case 'Feb':
                return 'Február';
                break;

            case 'Mar':
                return 'Marec';
                break;

            case 'Apr':
                return 'Apríl';
                break;

            case 'Jun':
                return 'Jún';
                break;

            case 'Jul':
                return 'Júl';
                break;

            case 'Aug':
                return 'August';
                break;

            case 'Sep':
                return 'September';
                break;

            case 'Oct':
                return 'Október';
                break;

            case 'Nov':
                return 'November';
                break;

            case 'Dec':
                return 'December';
                break;

            case 'Jan':
                return 'Január';
                break;
        }
    }

    public function travelTypeStr($val)
    {
        switch($val)
        {
            case 'passenger':
                return 'Pridám sa k jazde';
                break;

            case 'car_company':
                return 'Pôjdem svojím služobným vozidlom';
                break;

            case 'car_personal':
                return 'Použijem súkromné vozidlo';
                break;

            case 'car_rental':
                return 'Chcem si zapožičať služobné vozidlo';
                break;

            case 'other':
                return 'Využijem iný typ dopravy';
                break;
        }
    }
}