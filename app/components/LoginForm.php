<?php
namespace Components;

use Nette,
    Nette\Application\UI\Form,
    Tracy;

class LoginForm extends Form
{
    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        $this->addText('login', 'Login')
            ->setRequired("Meno nesmie byt prazdne!");

        $this->addPassword('password', 'Heslo')
            ->setRequired("Heslo nesmie byt prazdne!");

        $this->addSubmit('submit', 'Prihlásiť');
    }
}