<?php

namespace App\Presenters;

use Nette,
    App\Models;


class HomepagePresenter extends BasePresenter
{
    /**
     * O nacitanie tohto sa postara Dependency Injection. Konkretna sluzba musi byt
     * nakonfigurovana v config.neon subore (naspodku v casti services)
     * o tom, ze to chceme nacitat do presenteru, rozhoduje ten flag @inject
     * Property MUSI byt public a accesibilna zvonku (kvoli DI sluzbe)
     *
     * @inject
     * @var Models\ExampleModel
     */
    public $model;


    public function renderDefault() {
        $values = $this->model->find();

        // priradim pole do sablony
        $this->template->dbData = $values;
    }

    /**
     * Toto je tovaren na komponenty. Je to vzor Factory a vsetko co potrebujeme je prefixnut
     * nazov funkcie "createComponentXXX" kde XXX je nazov komponenty ktorym ju volame v sablonach presenteru
     *
     * @param $name string
     * @return \Components\UserForm
     */
    public function createComponentNazovMojejKomponenty($name)
    {
        return new \Components\UserForm($this, $name);
    }
}
