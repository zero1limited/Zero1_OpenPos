<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as PosHelper;
use Zero1\OpenPos\Helper\Order as OpenPosOrderHelper;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Zero1\OpenPos\Model\PaymentMethod\Layaways;

class PaymentCreate
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var OpenPosOrderHelper
     */
    protected $openPosOrderHelper;

    /**
     * @param PosHelper $posHelper
     * @param OpenPosOrderHelper $openPosOrderHelper
     */
    public function __construct(
        PosHelper $posHelper,
        OpenPosOrderHelper $openPosOrderHelper
    ) {
        $this->posHelper = $posHelper;
        $this->openPosOrderHelper = $openPosOrderHelper;
    }

    /**
     * Create OpenPOS payment for orders that are not layaways.
     * 
     * @param OrderManagementInterface $orderManagement
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(OrderManagementInterface $orderManagement, OrderInterface $order): OrderInterface
    {
        if($this->posHelper->isPosOrder($order)) {
            if($order->getPayment()->getMethod() === Layaways::PAYMENT_METHOD_CODE) {
                return $order;
            }

            $this->openPosOrderHelper->makePayment($order, $order->getGrandTotal(), $order->getPayment()->getMethod(), $order->getPayment()->getMethod());
        }

        return $order;
    }
}
