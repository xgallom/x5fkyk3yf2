<?php
namespace App\Models;

use Nette;

class CustomerModel extends BaseModel
{
    /**
     * @var string
     */
    protected $_table = 'customer';


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