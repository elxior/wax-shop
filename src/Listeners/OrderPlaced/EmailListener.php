<?php

namespace Wax\Shop\Listeners\OrderPlaced;

use Illuminate\Support\Facades\Mail;
use Wax\Core\Support\ConfigurationDatabase;
use Wax\Shop\Events\OrderPlacedEvent;
use Wax\Shop\Mail\OrderPlaced;

class EmailListener
{
    public function handle(OrderPlacedEvent $event)
    {
        $order = $event->order();

        if (empty($order->email)) {
            return false;
        }

        // Customer Email
        Mail::to($order->email)
            ->send(new OrderPlaced($order));

        // Admin Email
        $mailSettings = app()->makeWith(ConfigurationDatabase::class, ['group' => 'Mail Settings']);
        $mailTo = $this->parseMailTo($mailSettings->get('WEBSITE_MAILTO'));
        Mail::to($mailTo)
            ->send(new OrderPlaced($order));
    }

    protected function parseMailTo($mailString)
    {
        return collect(preg_split('/[\s,]+/', $mailString))->filter();
    }
}
