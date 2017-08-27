<?php
namespace App\Models;

use Nette;

class TravelTypeModel extends BaseModel
{
    /**
     * @var string
     */
    protected $_table = 'travel_type';


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