<?php

namespace Wax\Shop\Payment\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Wax\Shop\Payment\Drivers\AuthorizeNetCimDriver;

class PaymentMethodRepository
{
    protected function getDriver()
    {
        return app()->make(AuthorizeNetCimDriver::class);
    }

    public function getBillingInfo()
    {
        return Auth::user()->billingInfo;
    }

    public function create($data)
    {
        $billingInfo = $this->getDriver()->createCard($data);

        Auth::user()->billingInfo()->save($billingInfo);
    }

    public function update($data, Model $billingInfo)
    {
        // talk to the payment gateway service here

        // save the local record
        $billingInfo->update([
            'masked_card_number' => substr($data['cardNumber'], -4),
            'expiration_date' => $data['expMonth'].'/'.$data['expYear'],
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'],
            'address' => $data['address'],
            'zip' => $data['zip'],
        ]);
    }

    public function delete(Model $billingInfo)
    {
        // talk to the payment gateway service here

        $billingInfo->delete();
    }
}
