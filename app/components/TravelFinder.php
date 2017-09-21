<?php
namespace Components;

use Nette,
    Nette\Application\UI\Form,
    Tracy;

class TravelFinder extends Form
{
    public function __construct($parent, $name, $cities, $default_values = null)
    {
        parent::__construct($parent, $name);

        if($default_values == null)
            $default_values = [
                'trip_type' => isset($_COOKIE['last_triptype']) ? ($_COOKIE['last_triptype'] ? 'true' : 'false'): 'true',
                'city_from' => isset($_COOKIE['last_from']) ? $_COOKIE['last_from'] : 'Bratislava',
                'city_to' => isset($_COOKIE['last_to']) ? $_COOKIE['last_to'] : 'Banská Bystrica'
            ];

        $this['trip_type'] = new \Nette\Forms\Controls\ButtonSelect('Druh jazdy', ['true' => 'Spiatočná', 'false' => 'Jednosmerná']);
        $this['trip_type']->setDefaultValue($default_values['trip_type']);

        $this->addText('city_from', 'Odkiaľ')
            ->setRequired("Mesto nesmie byt prazdne!")
            ->setHtmlAttribute('city_autofill', 'true')
            ->addRule(Form::EQUAL, 'Zadajte existujúce mesto', $cities)
            ->setDefaultValue($default_values['city_from']);

        $this->addText('city_to', 'Kam')
            ->setRequired("Mesto nesmie byt prazdne!")
            ->setHtmlAttribute('city_autofill', 'true')
            ->addRule(Form::EQUAL, 'Zadajte existujúce mesto', $cities)
            ->setDefaultValue($default_values['city_to']);

        $this->addSubmit('submit', 'Vyhladať spojenie');
    }
}