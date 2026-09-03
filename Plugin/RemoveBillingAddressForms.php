<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Zero1\OpenPos\Model\TillSessionManagement;

class RemoveBillingAddressForms
{
    const BILLING_ADDRESS_COMPONENT = 'Magento_Checkout/js/view/billing-address';

    const PAYMENT_CONTAINERS = ['payments-list', 'afterMethods'];

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        TillSessionManagement $tillSessionManagement
    ) {
        $this->tillSessionManagement = $tillSessionManagement;
    }

    /**
     * A POS quote already has its billing address set server side, so remove the billing address forms from the checkout page.
     *
     * @param LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, array $result): array
    {
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            return $result;
        }

        $steps = $result['components']['checkout']['children']['steps']['children'] ?? [];
        if(!isset($steps['billing-step']['children']['payment']['children'])) {
            return $result;
        }

        $payment = $steps['billing-step']['children']['payment']['children'];

        foreach(self::PAYMENT_CONTAINERS as $container) {
            if(!isset($payment[$container]['children'])) {
                continue;
            }

            foreach($payment[$container]['children'] as $name => $component) {
                if(($component['component'] ?? null) === self::BILLING_ADDRESS_COMPONENT) {
                    unset($payment[$container]['children'][$name]);
                }
            }
        }

        $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children'] = $payment;

        return $result;
    }
}
