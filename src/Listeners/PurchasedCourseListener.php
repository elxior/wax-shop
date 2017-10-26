<?php

namespace Wax\Shop\Listeners;

use App\HubSpot\HubSpot;
use Wax\Shop\Events\PurchasedCourse;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurchasedCourseListener implements ShouldQueue
{
    /**
     * @var HubSpot
     */
    public $hubSpot;

    /**
     * Create API instance
     *
     * UserToCRM constructor.
     */
    public function __construct()
    {
        $this->hubSpot = new HubSpot();
    }

    public function handle(PurchasedCourse $purchasedCourse)
    {
        try {
            $this->hubSpot->addContactsToList(
                $purchasedCourse->user()->email,
                $purchasedCourse->course()->name
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
