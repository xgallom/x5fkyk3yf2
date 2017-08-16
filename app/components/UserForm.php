<?php
namespace Components;

use Nette,
    Nette\Application\UI\Form;

class UserForm extends Form
{
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        $this->addText('login', 'Login (email)')
            ->setRequired("Login nesmie byť prázdny!");

        $this->addText('password', 'Heslo')
            ->setRequired("Heslo nesmie byt prazdne!");

        $this->addCheckbox('aktivny', 'Aktívny');

        $this->addSubmit('submit', 'Uložiť');
    }
}