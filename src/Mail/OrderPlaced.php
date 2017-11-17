<?php

namespace Wax\Shop\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Wax\Core\Support\ConfigurationDatabase;
use Wax\Shop\Models\Order;

class OrderPlaced extends Mailable
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

        return $this->from($mailSettings->get('WEBSITE_MAILFROM'), config('app.name'))
            ->subject(__('shop::mail.order_placed_subject'))
            ->view('emails.order_placed', ['order' => $this->order->toArray()]);
    }

}
