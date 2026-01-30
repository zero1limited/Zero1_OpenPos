<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Sales\Model\Order;
use Zero1\OpenPos\Model\PaymentMethod\Layaways;

class InvoiceBlocker
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param OrderManagement $orderManagement
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        OrderManagement $orderManagement
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Ensure layaway OpenPOS orders cannot be invoiced if not fully paid.
     *
     * @param Order $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanInvoice(Order $subject, bool $result): bool
    {
        // Check order is an OpenPOS order
        if($this->orderManagement->isPosOrder($subject)) {

            // Check the order is layaways
            if($subject->getPayment()->getMethod() !== Layaways::PAYMENT_METHOD_CODE) {
                return $result;
            }

            // Cannot invoice, total has not been paid.
            if(!$this->orderManagement->isOrderPaid($subject)) {
                return false;
            }
        }

        return $result;
    }
}
