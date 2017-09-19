<?php

namespace App\Presenters;

use Nette,
    App\Models,
    Nette\Application\UI;


class MailPresenter extends BasePresenter
{
    /**
     * @inject
     * @var Models\CityModel
     */
    public $cityModel;

    /**
     * @inject
     * @var Models\CustomerModel
     */
    public $customerModel;

    /**
     * @inject
     * @var Models\TravelModel
     */
    public $travelModel;

    /**
     * @inject
     * @var Models\TravelTypeModel
     */
    public $travelTypeModel;

    /**
     * @inject
     * @var Models\TripModel
     */
    public $tripModel;

    private $outcome = "unknown";
    public function actionConfirm($customer) {
        $this->outcome = "error";
        if($customer) {
            $this->outcome =
                $this->customerModel->table()->where('token_confirm', $customer)->update([
                    'is_confirmed' => true
                ]) ? "success" : "error";
        }
    }

    public function renderConfirm($customer) {
        $this->template->outcome = $this->outcome;
    }
}