<?php

namespace App\Listeners;

use App\Events\SomeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventListener
{
    public function __construct()
    {
        //
    }

    public function handle(PurchaseProductEvent $event)
    {
        $event->product->handlers->each->handle();
    }
}
