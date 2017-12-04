<?php

namespace Wax\Shop\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Wax\Core\Support\ConfigurationDatabase;
use Wax\Shop\Models\Order;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $mailSettings = app()->makeWith(ConfigurationDatabase::class, ['group' => 'Mail Settings']);

        $shipments = $this->order->shipments()
            ->whereNotNull('tracking_number')
            ->where('tracking_number', '!=', '')
            ->get()
            ->toArray();

        return $this->from($mailSettings->get('WEBSITE_MAILFROM'), config('app.name'))
            ->subject(__('shop::mail.order_shipped_subject'))
            ->view('shop::mail.order-shipped', ['order' => $this->order->toArray(), 'trackedShipments' => $shipments]);
    }
}
