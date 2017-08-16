<?php
namespace App\Models;

use Nette;

class ExampleModel extends BaseModel
{
    /**
     * @var string
     */
    protected $_table = 'test';


    /**
     * Constructor
     *
     * @param Nette\Database\Context    $db
     */
    public function __construct(Nette\Database\Context $db)
    {
        parent::__construct($db);
    }
}