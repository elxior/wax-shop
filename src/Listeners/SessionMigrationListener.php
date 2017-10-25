<?php

namespace App\Shop\Listeners;

use App\Shop\Models\Order;
use Wax\Core\Events\SessionMigrationEvent;

class SessionMigrationListener
{
    /**
     * Handle the event.
     *
     * @param  SessionMigrationEvent  $event
     * @return void
     */
    public function handle(SessionMigrationEvent $event)
    {
        Order::where('session_id', $event->getOldId())
            ->update(['session_id' => $event->getNewId()]);
    }
}
