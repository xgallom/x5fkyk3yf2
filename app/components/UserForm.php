<?php
namespace Components;

use Nette,
    Nette\Application\UI\Form;

class UserForm extends Form
{
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        $this->addEmail('email', 'Login (E-Mail)')
            ->setRequired("Login nesmie byť prázdny!");

        $this->addText('city_from', 'Miesto odchodu:')
            ->setRequired("Mesto nesmie byt prazdne!");

        $this->addText('date_from', 'Datum odchodu:')
            ->setRequired("Datum nesmie byt prazdny!");

        $this->addText('city_to', 'Cielove miesto:')
            ->setRequired("Mesto nesmie byt prazdne!");

        $this->addText('date_to', 'Datum navratu:')
            ->setRequired("Datum nesmie byt prazdny!");

        $this->addSubmit('submit', 'Podme planovat!');
    }
}