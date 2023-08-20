<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Readdle\StripeHttpClientMock\Error\ResourceMissing;
use Readdle\StripeHttpClientMock\ResponseInterface;
use Readdle\StripeHttpClientMock\TestCards\TestCardRegistry;

class PaymentIntent extends AbstractEntity
{
    protected array $props = [
        'amount'                      => 0,
        'amount_capturable'           => 0,
        'amount_details'              => [],
        'amount_received'             => 0,
        'application'                 => null,
        'application_fee_amount'      => null,
        'automatic_payment_methods'   => null,
        'canceled_at'                 => null,
        'cancellation_reason'         => null,
        'capture_method'              => '',
        'charges'                     => [],
        'client_secret'               => '',
        'confirmation_method'         => '',
        'created'                     => 0,
        'currency'                    => '',
        'customer'                    => null,
        'description'                 => null,
        'invoice'                     => null,
        'last_payment_error'          => null,
        'livemode'                    => false,
        'metadata'                    => [],
        'next_action'                 => null,
        'on_behalf_of'                => null,
        'payment_method'              => null,
        'payment_method_options'      => [
            'card' => [
                'installments'           => null,
                'mandate_options'        => null,
                'network'                => null,
                'request_three_d_secure' => '',
            ],
        ],
        'payment_method_types'        => [],
        'processing'                  => null,
        'receipt_email'               => null,
        'review'                      => null,
        'setup_future_usage'          => null,
        'shipping'                    => null,
        'statement_descriptor'        => null,
        'statement_descriptor_suffix' => null,
        'status'                      => 'requires_confirmation',
        'transfer_data'               => null,
        'transfer_group'              => null,
    ];

    protected static array $expandableProps = [
        'payment_method',
        'invoice',
    ];

    protected static array $subActions = [
        'cancel' => 'cancel',
        'confirm' => 'confirm',
    ];

    public static function prefix(): string
    {
        return 'pi';
    }

    public function cancel(array $params): ResponseInterface
    {
        $validStatuses = ['requires_payment_method', 'requires_capture', 'requires_confirmation', 'requires_action', 'processing'];

        if (!in_array($this->props['status'], $validStatuses)) {
            return new ResourceMissing();
        }

        $this->props['status'] = 'canceled';

        if (array_key_exists('cancellation_reason', $params)) {
            $this->props['cancellation_reason'] = $params['cancellation_reason'];
        }

        return $this;
    }

    public function confirm(array $params): ResponseInterface
    {
        $validStatuses = ['requires_payment_method', 'requires_capture', 'requires_confirmation', 'requires_action', 'processing'];

        $cardRegistry = new TestCardRegistry();

        $paymentMethod = $params['payment_method'] ?? $this->props['payment_method'] ?? null;

        if ($paymentMethod) {
            $card = $cardRegistry->getCard(null, $paymentMethod);
            if ($card) {
                return $card->createConfirmResult($this);
            } else {
                // we let the request go through, because this does not change the default behavior.
            }
        }

        if (!in_array($this->props['status'], $validStatuses)) {
            return new ResourceMissing();
        }

        $this->props['status'] = 'succeeded';

        return $this;

    }
}
