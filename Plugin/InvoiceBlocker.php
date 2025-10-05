<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as PosHelper;
use Zero1\OpenPos\Model\ResourceModel\Payment\CollectionFactory as PaymentCollectionFactory;
use Magento\Sales\Model\Order;

class InvoiceBlocker
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var PaymentCollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @param PosHelper $posHelper
     * @param PaymentCollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        PosHelper $posHelper,
        PaymentCollectionFactory $paymentCollectionFactory
    ) {
        $this->posHelper = $posHelper;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
    }

    /**
     * Ensure layaway OpenPOS orders cannot be invoiced if not fully paid.
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanInvoice(Order $subject, bool $result): bool
    {
        // Check order is an OpenPOS order
        if($this->posHelper->isPosOrder($subject)) {
            // Check order is layaways
            $paymentMethod = $subject->getPayment()->getMethod();
            if($paymentMethod !== 'openpos_layaways') { // todo fix
                return $result;
            }

            $paymentCollection = $this->paymentCollectionFactory->create();
            $paymentCollection->addFieldToFilter('order_id', ['eq' => $subject->getId()]);

            $totalPaid = 0;
            foreach($paymentCollection as $payment) {
                $totalPaid += $payment->getBasePaymentAmount();
            }

            if($totalPaid < $subject->getGrandTotal()) {
                // Cannot invoice, total has not been paid.
                return false;
            }
        }

        return $result;
    }
}
