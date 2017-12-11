<?php

namespace Wax\Shop\Listeners;

use Wax\Shop\Repositories\OrderRepository;
use Wax\Core\Events\SessionMigrationEvent;

class SessionMigrationListener
{
    protected $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * Handle the event.
     *
     * @param  SessionMigrationEvent  $event
     * @return void
     */
    public function handle(SessionMigrationEvent $event)
    {
        $this->orderRepo->getOrderModel()
            ->where('session_id', $event->getOldId())
            ->update(['session_id' => $event->getNewId()]);
    }
}
