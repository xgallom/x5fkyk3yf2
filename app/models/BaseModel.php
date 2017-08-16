<?php
namespace App\Models;

use Nette;

class BaseModel extends Nette\Object
{
    /**
     * @var Nette\Database\Context
     */
    protected $db;

    /**
     * @var string
     */
    protected $_table = null;



    /**
     * Constructor.
     * @param   Nette\Database\Context	$db
     */
    public function __construct(\Nette\Database\Context $db)
    {
        $this->db = $db;
    }

    /**
     * Obalka nad prikazom table. Aby som stale nemusel vypisovat
     * $this->db->table('model_table')->nieco ale rovno $this->table()->ides
     *
     * @return  Nette\Database\Table\Selection
     * @throws  \Exception
     */
    public function table()
    {
        if ($this->_table == null) {
            throw new \Exception('Nieje zadefinovaná "_table" pre model!');
        }

        return $this->db->table($this->_table);
    }



    /**
     * Alias na table()
     *
     * @return  Nette\Database\Table\Selection
     */
    public function find()
    {
        return $this->table();
    }



    /**
     * Vracia DB connection nad ktorou je mozne robit queries
     *
     * @return  Nette\Database\Connection
     */
    public function db()
    {
        return $this->db;
    }
}