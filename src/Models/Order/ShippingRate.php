<?php

namespace App\Shop\Models\Order;

use Illuminate\Database\Eloquent\Model;

/**
 * A shipping rate quote from a carrier rate lookup or other calculation
 *
 * @property string $carrier The name of the shipping carrier, e.g. 'USPS'.
 * @property string $service_name The name of the service, e.g. '3-Day Select'.
 * @property string $service_code The carrier's code for the service, e.g. '3DS'.
 * @property int $business_transit_days The Time In Transit provided by the carrier.
 * @property float $amount The total cost for the service.
 * @property int $box_count Estimated number of boxes required for the shipment, dependent on the carrier's size rules
 * @property string $packaging Notes on the calculated box sizes for the shipment.
 */
class ShippingRate extends Model
{
    protected $table = 'shipping_rates';
    protected $fillable = [
        'carrier',
        'service_name',
        'service_code',
        'business_transit_days',
        'amount',
        'box_count',
        'packaging'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
