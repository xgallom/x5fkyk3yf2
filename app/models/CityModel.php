<?php
namespace App\Models;

use Nette;

class CityModel extends BaseModel
{
    /**
     * @var string
     */
    protected $_table = 'city';


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