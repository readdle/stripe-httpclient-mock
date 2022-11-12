<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Exception;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\ResponseInterface;

class SetupIntent extends AbstractEntity
{
    protected array $props = [
        'application'            => null,
        'cancellation_reason'    => null,
        'client_secret'          => null,
        'created'                => null,
        'customer'               => null,
        'description'            => null,
        'flow_directions'        => null,
        'last_setup_error'       => null,
        'latest_attempt'         => null,
        'livemode'               => false,
        'mandate'                => null,
        'metadata'               => [],
        'next_action'            => null,
        'on_behalf_of'           => null,
        'payment_method'         => null,
        'payment_method_options' => [],
        'payment_method_types'   => [],
        'single_use_mandate'     => null,
        'status'                 => null,
        'usage'                  => null,
    ];

    /**
     * @throws Exception
     */
    public static function create(string $id, array $props = []): ResponseInterface
    {
        if (array_key_exists('mandate_data', $props)) {
            $mandate = EntityManager::createEntity('mandate', $props['mandate_data']);
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $props['mandate'] = $mandate->id;
            unset($props['mandate_data']);
        }

        if (array_key_exists('payment_method_data', $props)) {
            /** @var PaymentMethod $paymentMethod */
            $paymentMethod = EntityManager::createEntity('payment_method', $props['payment_method_data']);
            /** @noinspection PhpUndefinedFieldInspection */
            $props['payment_method'] = $paymentMethod->id;
            unset($props['payment_method_data']);

            /** @noinspection PhpUndefinedFieldInspection */
            if ($paymentMethod->type === 'alipay' && array_key_exists('return_url', $props)) {
                $props['next_action'] = [
                    'alipay_handle_redirect' => [
                        'return_url' => $props['return_url'],
                    ],
                ];
            }
        }

        return parent::create($id, $props);
    }
}
