<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

/**
 * Embedded value object at Invoice::$parent->subscription_details (Stripe API "Basil") - see
 * InvoiceParent for why this is modeled as an AbstractEntity subclass despite not being a real,
 * independently-addressable Stripe resource. `subscription` is the one field here Stripe actually
 * expands from an ID into a full object on request, same as any other $expandableProps entry.
 */
class InvoiceSubscriptionDetails extends AbstractEntity
{
    protected array $props = [
        'metadata'                    => null,
        'subscription'                => null,
        'subscription_proration_date' => null,
    ];

    protected static array $expandableProps = [
        'subscription',
    ];

    public function toArray(): array
    {
        $array = parent::toArray();
        unset($array['id'], $array['object']);

        return $array;
    }
}
