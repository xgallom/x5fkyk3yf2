<?php

namespace App\Presenters;

use Nette,
    App\Models,
    Nette\Application\UI;


class HomepagePresenter extends BasePresenter
{
    /**
     * O nacitanie tohto sa postara Dependency Injection. Konkretna sluzba musi byt
     * nakonfigurovana v config.neon subore (naspodku v casti services)
     * o tom, ze to chceme nacitat do presenteru, rozhoduje ten flag @inject
     * Property MUSI byt public a accesibilna zvonku (kvoli DI sluzbe)
     *
     * @inject
     * @var Models\TravelModel
     */
    public $travelModel;

    /**
     * @inject
     * @var Models\CityModel
     */
    public $cityModel;

    public function renderDefault() {
    }

    public function renderShow() {

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

        $form = new \Components\TravelFinder($this, $name, $cities);
        $form->onSuccess[] = [$this, 'travelFinderSucceeded'];
        return $form;
    }

    public function travelFinderSucceeded(UI\Form $form, $values)
    {
        $this->redirectUrl('search/show' .
            '?city_from=' . $values['city_from'] .
            '&city_to=' . $values['city_to'] .
            '&trip_type=' . $values['trip_type']
        );
    }
}
