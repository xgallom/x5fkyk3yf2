<?php
namespace App\Models;

use Nette;

class TripModel extends BaseModel
{
    /**
     * @var string
     */
    protected $_table = 'trip';


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