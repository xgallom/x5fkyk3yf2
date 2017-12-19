<?php

namespace App\Presenters;

use Nette,
    App\Models,
    Nette\Application\UI,
    Nette\Utils\DateTime,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer,
    Tracy\Debugger;


class MailPresenter extends BasePresenter
{
    /**
     * @inject
     * @var Nette\Mail\SendmailMailer
     */
    public $sendmailMailer;

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
                $this->customerModel->table()->where('token_confirm LIKE ?', '%' . $customer . '%')->update([
                    'is_confirmed' => true
                ]) ? "success" : "error";

            if($this->outcome == "success") {
                $rowCustomer = $this->customerModel->table()->where('token_confirm LIKE ?', '%' . $customer . '%')->fetch();
                $phone = $rowCustomer->phone;
                $firstName = $rowCustomer->name_first;
                $lastName = $rowCustomer->name_last;
                $email = $rowCustomer->email;
                include('emails/config.php');

                foreach($rowCustomer->related('trip.customer_id')->fetchAll() as $trip) {
                    $__t = $trip->related('travel.trip_id');
                    $travel0 = $__t->fetch();
                    $travel1 = $__t->fetch();

                    $cityFrom = $travel0->city_from->name;
                    $cityTo = $travel0->city_to->name;
                    $tripType = $travel1 == null ? 'false' : 'true';

                    $departure0 = DateTime::from($travel0->departure)->format('Y-m-d');
                    $travelType0 = $travel0->travel_type->name;
                    $travelProvider0 = $travel0->travel_provider_id;
                    $departureTime0 = DateTime::from($travel0->departure)->format('H:i');
                    $spots0 = $travel0->spots;

                    if($tripType == 'true') {
                        $departure1 = DateTime::from($travel1->departure)->format('Y-m-d');
                        $travelType1 = $travel1->travel_type->name;
                        $travelProvider1 = $travel1->travel_provider_id;
                        $departureTime1 = DateTime::from($travel1->departure)->format('H:i');
                        $spots1 = $travel1->spots;
                    }

                    $tokenRemove = $trip->token_remove;

                    include('emails/emails_added.php');
                }
            }
        }
    }

    public function renderConfirm($customer) {
        $this->template->outcome = $this->outcome;
        $this->template->deviceWidth = true;
    }

    public function actionNotify() {
        $allTravels = $this->travelModel->table()
            ->where('travel.is_approved IS TRUE OR (travel.is_approved IS NULL AND trip.is_approved IS TRUE)')
            ->where('departure::date = now()::date + interval \'1 day\'')
            ->where('trip.was_notified', false);


        $allTravels = $allTravels->fetchAll();
        foreach($allTravels as $tr) {
            include('emails/config.php');

            $val = $this->tripModel->table()->get($tr->trip);
            if($val->was_notified == true)
                continue;

            $travels = $val->related('travel.trip_id')
                ->where('travel.is_approved IS TRUE OR (travel.is_approved IS NULL AND trip.is_approved IS TRUE)');
            $travel0 = $travels->fetch();
            $travel1 = $travels->fetch();

            if(!$travel0)
                continue;

            $mail = new Message();
            $mail->setFrom($sender)
//                ->setSubject('Už sa to blíži, ' . ($travel0->travel_type->name == 'passenger' ? 'vaša' : 'tvoja') . ' cesta už zajtra')
                ->addTo($val->customer->email);

            include('emails/emails_notify.php');

            if($emailsHtml)
                $mail->setHtmlBody($body);
            else
                $mail->setBody($body);

            $this->sendmailMailer->send($mail);

            $this->tripModel->table()->where('id', $val->id)->update([
                'was_notified' => true
            ]);
        }
    }

    public function renderNotify() {

    }
}