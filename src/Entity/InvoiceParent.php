<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

/**
 * Embedded value object at Invoice::$parent (Stripe API "Basil", replacing Invoice's old top level
 * `subscription` field). It's never independently created or retrieved via its own REST endpoint, but
 * modeled as an AbstractEntity subclass anyway so the generic expand walker in
 * EntityManager::expand() can recurse through `parent.subscription_details.subscription` the same
 * way it recurses through any other entity's expandable chain (see $expandableProps/howToExpand()),
 * rather than that logic being special-cased for Invoice.
 */
class InvoiceParent extends AbstractEntity
{
    protected array $props = [
        'type'                 => null,
        'subscription_details' => null,
    ];

    protected static array $expandableProps = [
        'subscription_details',
    ];

    public function toArray(): array
    {
        $array = parent::toArray();
        unset($array['id'], $array['object']);

        return $array;
    }
}
