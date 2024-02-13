<?php

namespace App\Rules;

use App\Models\DeliveryNoteLine;
use Illuminate\Contracts\Validation\DataAwareRule;

class DeliveredItemRule implements DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $deliveredItem = $value;

        $deliveryNoteLine = DeliveryNoteLine::find($deliveredItem['deliveryNoteLineId']);

        $totalDeliveredQuantity = $deliveryNoteLine->deliveredItems->sum('delivered_quantity') + $deliveredItem['deliveredQuantity'];

        if ($totalDeliveredQuantity >= $deliveryNoteLine['quantity']) {
            $fail('Total delivered quantity for all deliveries cannot exceed the delivery note quantity ' . $deliveryNoteLine['quantity']);
        }

        if (!isset($deliveredItem['reason']) &&($deliveryNoteLine['quantity'] != $deliveredItem['deliveredQuantity'])) {
            $fail('Reason is required');
        }

        if (!isset($deliveredItem['deliveryPhotoExtension']) && isset($deliveredItem['deliveryPhoto'])) {
            $fail('Delivery photo extension is required');
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
