<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as PosHelper;
use Zero1\OpenPos\Helper\Order as OpenPosOrderHelper;
use Magento\Sales\Model\Order;
use Zero1\OpenPos\Model\PaymentMethod\Layaways;

class InvoiceBlocker
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
     * Ensure layaway OpenPOS orders cannot be invoiced if not fully paid.
     *
     * @param Order $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanInvoice(Order $subject, bool $result): bool
    {
        // Check order is an OpenPOS order
        if($this->posHelper->isPosOrder($subject)) {

            // Check the order is layaways
            if($subject->getPayment()->getMethod() !== Layaways::PAYMENT_METHOD_CODE) {
                return $result;
            }

            // Cannot invoice, total has not been paid.
            if(!$this->openPosOrderHelper->isOrderPaid($subject)) {
                return false;
            }
        }

        return $result;
    }
}
