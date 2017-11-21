<?php

namespace Wax\Shop\Models\Order;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Wax\Shop\Events\OrderChanged\CartContentsChangedEvent;
use Wax\Shop\Events\OrderChanged\ShippingAddressChangedEvent;
use Wax\Shop\Events\OrderChanged\ShippingServiceChangedEvent;
use Wax\Shop\Exceptions\ValidationException;
use Wax\Shop\Facades\ShopServiceFacade;
use Wax\Shop\Mail\OrderShipped;
use Wax\Shop\Models\Order;
use Wax\Shop\Models\Product;
use Wax\Shop\Tax\Contracts\TaxDriverContract;
use Wax\Shop\Tax\Support\Address;
use Wax\Shop\Tax\Support\LineItem;
use Wax\Shop\Tax\Support\Request;
use Wax\Shop\Tax\Support\Shipping;
use Wax\Shop\Validators\CreateOrderItemValidator;
use Wax\Shop\Validators\DeleteOrderItemValidator;
use Wax\Shop\Validators\OrderItemQuantityValidator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * A Shipment is a subset of an order having a distinct delivery destination/time. All cart items are
 * associated with a Shipment.
 *
 * @property Order $order The Order containing this shipment.
 * @property Collection|Item[] $items The items/products contained in this shipment, i.e. the "cart"
 * @property Collection|ShippingRate[] $rates The available shipping services/rates quoted for the shipment
 *
 * @property int $item_count Number of distinct products in the shipment.
 * @property int $total_quantity Sum of all item quantities in the shipment.
 * @property float $discountable_total Total price of all discountable items in the cart. Used for calculating the
 *      minimum order threshold for coupons.
 *
 * @property float $item_gross_subtotal Total of all line item prices. sum(price * quantity)
 * @property float $item_subtotal Total of all line item prices after coupons are applied.
 * @property float $item_discount_amount Total coupon value applied to the cart items.
 *
 * @property float $flat_shipping_subtotal Total of all flat shipping charges for the shipment.
 * @property float $shipping_gross_subtotal Sum of all flat-shipping and carrier rates for the cart.
 * @property float $shipping_discount_amount Total coupon value applied to shipping charges.
 * @property float $shipping_subtotal Sum of shipping charges after coupons are applied.
 *
 * @property float $gross_total Total price for the shipment. Cart subtotal + shipping + tax.
 * @property float $total Total price for the shipment after coupons are applied.
 *
 * @property bool $require_carrier Does this shipment require a shipping service / carrier rate lookup
 * @property string $shipping_carrier The selected shipping carrier.
 * @property string $shipping_service_code The selected shipping service code.
 * @property string $shipping_service_name The selected shipping service name.
 * @property float $shipping_service_amount The total cost for the selected shipping service.
 * @property int $business_transit_days The Time In Transit provided by the carrier.
 * @property int $box_count Estimated number of boxes required for the shipment, dependent on the carrier's size rules
 * @property string $packaging Notes on the calculated box sizes for the shipment.
 *
 * @property string $tax_desc Name of tax jurisdiction.
 * @property float $tax_rate Percentage for tax calculation.
 * @property bool $tax_shipping Should the shipping cost be included in the tax calculation?
 * @property float $tax_amount Total tax calculated for the shipment.
 *
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $phone
 * @property string $company
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $country Two-letter country code
 */
class Shipment extends Model
{
    protected $table = 'order_shipments';
    protected $with = [
        'items',
        'rates',
    ];
    protected $casts = [
        'tax_shipping' => 'boolean',
        'shipped_at' => 'timestamp',
        'desired_delivery_date' => 'timestamp',
    ];
    protected $hidden = ['items'];

    protected $fillable = [
        'firstname',
        'lastname',
        'phone',
        'email',
        'company',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
    ];


    protected $appends = [
        /**
         * You might want this stuff for a multi-shipment order
         */
//        'item_count',
//        'total_quantity',
//
//        'item_gross_subtotal',
//        'item_discount_amount',
//        'item_subtotal',
//
//        'flat_shipping_subtotal',
//        'shipping_gross_subtotal',
//        'shipping_subtotal',
//
//        'gross_total',
//        'total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function rates()
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function combineDuplicateItems()
    {
        $this->items
            ->sortByDesc('created_at')
            ->each(function ($item) {
                $options = $item->options->mapWithKeys(function ($option) {
                    return [$option->option_id => $option->value_id];
                })->toArray();

                if (($duplicate = $this->findItem($item->product_id, $options, [])) && $item->isNot($duplicate)) {
                    $duplicate->quantity += $item->quantity;
                    $duplicate->save();
                    $item->delete();
                }
            });
    }

    /**
     * @param int $productId
     * @param int $quantity
     * @param array $options
     * @param array $customizations
     * @throws ValidationException
     */
    public function addItem(int $productId, int $quantity = 1, array $options = [], array $customizations = [])
    {
        if ($item = $this->findItem($productId, $options, $customizations)) {
            $this->updateItemQuantity($item->id, $item->quantity + $quantity);
            return;
        }

        (new CreateOrderItemValidator($productId, $quantity, $options, $customizations))
            ->validate();

        $item = new Item([
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
        $this->items()->save($item);

        foreach ($options as $optionId => $valueId) {
            $item->options()->create([
                'option_id' => $optionId,
                'value_id' => $valueId,
            ]);
        }

        event(new CartContentsChangedEvent($this->order->fresh()));
    }

    public function updateItemQuantity(int $itemId, int $quantity)
    {
        (new OrderItemQuantityValidator($itemId, $quantity))
            ->validate();

        $item = $this->items->where('id', $itemId)->first();
        $item->quantity = $quantity;
        $item->save();

        event(new CartContentsChangedEvent($this->order));
    }

    public function deleteItem(int $itemId)
    {
        (new DeleteOrderItemValidator($itemId))
            ->validate();

        $item = $this->items
            ->where('id', $itemId)
            ->first();

        if ($item->delete() !== true) {
            throw new \Exception('Unknown error deleting order item');
        }

        event(new CartContentsChangedEvent($this->order->fresh()));
    }

    /**
     * Find the given product in the shipment. Options and Customizations can be omitted (null) to match the product
     * with ANY options. Pass an empty array to match the product with NO options.
     *
     * @param int $productId
     * @param array|null $options
     * @param array|null $customizations
     * @return mixed
     */
    public function findItem(int $productId, array $options = null, array $customizations = null)
    {
        $items =  $this->items
            ->where('product_id', $productId);

        if (!is_null($options)) {
            $items = $items->load('options')
                ->filter(function ($item) use ($options) {
                    return $item->options->mapWithKeys(function ($option) {
                            return [$option->option_id => $option->value_id];
                    })->toArray() == $options;
                });
        }

        if ($customizations) {
            /* @TODO */
        }

        return $items->first();
    }

    public function setAddress(
        string $firstName,
        string $lastName,
        string $company,
        string $email,
        string $phone,
        string $address1,
        string $address2,
        string $city,
        string $state,
        string $zip,
        string $countryCode
    ) {
        if (!preg_match('/^([a-z]{2})?$/i', $countryCode)) {
            throw new \Exception("Invalid country code");
        }

        $this->firstname = $firstName;
        $this->lastname = $lastName;
        $this->company = $company;
        $this->email = $email;
        $this->phone = $phone;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city = $city;
        $this->state = $state;
        $this->zip = $zip;
        $this->country = $countryCode;

        $this->save();

        event(new ShippingAddressChangedEvent($this->order->fresh()));
    }

    public function setShippingService(ShippingRate $rate)
    {
        $this->shipping_carrier = $rate->carrier;
        $this->shipping_service_code = $rate->service_code;
        $this->shipping_service_name = $rate->service_name;
        $this->shipping_service_amount = $rate->amount;
        $this->business_transit_days = $rate->business_transit_days;
        $this->box_count = $rate->box_count;
        $this->packaging = $rate->packaging;
        $result = $this->save();

        event(new ShippingServiceChangedEvent($this->order->fresh()));

        return $result;
    }

    public function setTrackingNumber(string $trackingNumber)
    {
        $now = Carbon::now();

        $this->tracking_number = $trackingNumber;
        $this->shipped_at = $now;
        $this->save();

        $order = $this->order->fresh();

        if ($order->shipments()->whereNull('shipped_at')->get()->isEmpty()) {
            $order->shipped_at = $now;
            $order->save();
        }

        // Customer Email
        if (!empty($order->email)) {
            Mail::to($order->email)
                ->send(new OrderShipped($order));
        }
    }

    public function isAddressSet()
    {
        return !is_null(
            $this->firstname
            ?? $this->lastname
            ?? $this->address1
            ?? $this->address2
            ?? $this->city
            ?? $this->state
            ?? $this->zip
            ?? $this->country
        );
    }

    public function calculateTax()
    {
        if (!$this->isAddressSet()) {
            return false;
        }

        /* @var TaxDriverContract $taxDriver */
        $taxDriver = app()->make(TaxDriverContract::class);

        $taxRequest = $this->buildTaxRequest();

        $taxResponse = $taxDriver->getTax($taxRequest);

        $this->tax_desc = $taxResponse->getDescription();
        $this->tax_amount = $taxResponse->getAmount();
        $this->tax_rate = $taxResponse->getRate();
        $this->tax_shipping = $taxResponse->getTaxShipping();

        $this->save();
    }

    public function invalidateTax()
    {
        $this->tax_desc = null;
        $this->tax_amount = null;
        $this->tax_rate = null;
        $this->tax_shipping = null;

        $this->save();
    }

    public function invalidateShipping()
    {
        $this->rates()->delete();

        $this->shipping_carrier = null;
        $this->shipping_service_code = null;
        $this->shipping_service_name = null;
        $this->shipping_service_amount = null;
        $this->shipping_discount_amount = null;
        $this->business_transit_days = null;

        $this->save();
    }

    public function commitTax()
    {
        if (!$this->isAddressSet()) {
            return false;
        }

        /* @var TaxDriverContract $taxDriver */
        $taxDriver = app()->make(TaxDriverContract::class);

        $taxRequest = $this->buildTaxRequest();

        return $taxDriver->commit($taxRequest);
    }

    protected function buildTaxRequest() : Request
    {
        $taxRequest = (new Request())
            ->setAddress(
                new Address(
                    $this->address1,
                    $this->address2,
                    null,
                    $this->city,
                    $this->state,
                    $this->zip,
                    $this->country
                )
            )
            ->setShipping(new Shipping($this->shipping_service_name, $this->shipping_service_amount));

        $this->items->each(function ($item) use ($taxRequest) {
            $taxRequest->addLineItem(new LineItem(
                $item->sku,
                $item->unit_price,
                $item->quantity,
                $item->taxable
            ));
        });

        return $taxRequest;
    }

    public function getRequireCarrierAttribute() : bool
    {
        return $this->items->contains('shipping_enable_rate_lookup', true);
    }

    public function getEnableTrackingNumberAttribute() : bool
    {
        return $this->items->contains('shipping_enable_tracking_number', true);
    }

    public function getItemCountAttribute() : int
    {
        return $this->items->count();
    }

    public function getTotalQuantityAttribute() : int
    {
        return $this->items->sum('quantity');
    }

    public function getDiscountableTotalAttribute() : float
    {
        return $this->items->where('discountable', 1)->sum('gross_subtotal');
    }

    public function getItemGrossSubtotalAttribute() : float
    {
        return $this->items->sum('gross_subtotal');
    }

    public function getItemSubtotalAttribute() : float
    {
        return $this->items->sum('subtotal');
    }

    public function getItemDiscountAmountAttribute() : float
    {
        return $this->items->sum('discount_amount');
    }

    public function getFlatShippingSubtotalAttribute() : float
    {
        return $this->items->sum('flat_shipping_subtotal');
    }

    public function getShippingGrossSubtotalAttribute() : float
    {
        return $this->flat_shipping_subtotal + $this->shipping_service_amount;
    }

    public function getShippingSubtotalAttribute() : float
    {
        return $this->shipping_gross_subtotal - $this->shipping_discount_amount;
    }

    public function getGrossTotalAttribute() : float
    {
        return $this->item_gross_subtotal + $this->shipping_gross_subtotal + $this->tax_amount;
    }

    public function getTotalAttribute() : float
    {
        return $this->item_subtotal + $this->shipping_subtotal + $this->tax_amount;
    }

    public function validateTax() : bool
    {
        return !is_null($this->tax_desc)
            && !is_null($this->tax_amount)
            && !is_null($this->tax_rate)
            && !is_null($this->tax_shipping);
    }

    public function validateShipping() : bool
    {
        if (!$this->require_carrier) {
            return true;
        }

        return !is_null($this->shipping_carrier)
            && !is_null($this->shipping_service_code)
            && !is_null($this->shipping_service_name)
            && !is_null($this->shipping_service_amount);
    }
}
