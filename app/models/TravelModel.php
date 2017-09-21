<?php
namespace App\Models;

use Nette;

class TravelModel extends BaseModel
{
    /**
     * @var string
     */
    protected $_table = 'travel';


    /**
     * Constructor
     *
     * @param Nette\Database\Context    $db
     */
    public function __construct(Nette\Database\Context $db)
    {
        parent::__construct($db);


    }

    public function getTravels($cityFromId, $cityToId, $departure)
    {
        $result = parent::db()->query(
'
SELECT t.departure, t.travel_type_name, t.customer_email, t.city_from_name, t.address, t.city_to_name, COUNT(1) AS taken_spots
FROM (
	SELECT (CASE WHEN t.travel_provider_id IS NULL THEN t.id ELSE t.travel_provider_id END) AS travel_provider_id
	FROM (
		SELECT t.*, tr.customer_id, (CASE WHEN t.is_approved IS NULL THEN tr.is_approved ELSE t.is_approved END) AS f_is_approved
		FROM trip as tr
		RIGHT JOIN (
			SELECT * FROM travel
		) AS t ON t.trip_id = tr.id
	) AS t WHERE f_is_approved IS TRUE
) AS tp
RIGHT JOIN (
	SELECT t.id AS id, t.departure AS departure, t.f_travel_type_name AS travel_type_name, t.f_customer_email AS customer_email,
		t.f_city_from_name AS city_from_name, t.f_address AS address, c.name AS city_to_name
	FROM city AS c
	RIGHT JOIN (
		SELECT t.*, c.name AS f_city_from_name, (CASE WHEN t.address IS NULL THEN c.default_address ELSE t.address END) AS f_address
		FROM city AS c
		RIGHT JOIN (
			SELECT t.*, cs.email AS f_customer_email
			FROM customer AS cs
			RIGHT JOIN (
				SELECT t.*, tt.name AS f_travel_type_name
				FROM travel_type AS tt
				RIGHT JOIN (
					SELECT t.*, tr.customer_id, (CASE WHEN t.is_approved IS NULL THEN tr.is_approved ELSE t.is_approved END) AS f_is_approved
					FROM trip as tr
					RIGHT JOIN (
						SELECT * FROM travel WHERE departure = "' . $departure . '" AND city_from_id = ' . $cityFromId . ' AND city_to_id = ' . $cityToId . '
					) AS t ON t.trip_id = tr.id
				) AS t ON t.travel_type_id = tt.id WHERE tt.is_provider IS TRUE
			) AS t ON t.customer_id = cs.id WHERE f_is_approved IS TRUE
		) AS t ON t.city_from_id = c.id
	) AS t ON t.city_to_id = c.id
) AS t ON tp.travel_provider_id = t.id
GROUP BY t.id, t.departure, t.travel_type_name, t.customer_email, t.city_from_name, t.address, t.city_to_name
ORDER BY t.departure ASC
'
        );

        return $result->fetchAll();
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

        // TODO: override
        return $this->db->table($this->_table);
    }
}