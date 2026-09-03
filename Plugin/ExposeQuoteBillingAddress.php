<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Zero1\OpenPos\Model\TillSessionManagement;

class ExposeQuoteBillingAddress
{
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
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result): array
    {
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            return $result;
        }

        if(isset($result['billingAddressFromData']) || !isset($result['shippingAddressFromData'])) {
            return $result;
        }

        // Reusing the shipping copy is safe: the only reason Magento dropped the
        // billing address is that the two were identical.
        $result['billingAddressFromData'] = $result['shippingAddressFromData'];
        $result['isBillingAddressFromDataValid'] = $result['isShippingAddressFromDataValid'] ?? true;

        return $result;
    }
}
