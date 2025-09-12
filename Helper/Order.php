<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Model\ResourceModel\Payment\CollectionFactory as PaymentCollectionFactory;


use Magento\Sales\Api\Data\OrderInterface;

/**
 * Work in progress
 */

class Order extends AbstractHelper
{
    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var PaymentCollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @param Context $context
     * @param PosHelper $openPosHelper
     * @param PaymentCollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        Context $context,
        OpenPosHelper $openPosHelper,
        PaymentCollectionFactory $paymentCollectionFactory
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        
        parent::__construct($context);
    }


    public function isOrderPaid(OrderInterface $order): bool
    {
        $totalPaid = 0.00;
        $grandTotal = $order->getBaseGrandTotal();

        $payments = $this->getPaymentsForOrder($order);
        foreach ($payments as $payment) {
            $totalPaid += (float)$payment->getBaseAmountPaid();
        }

        if($totalPaid >= $grandTotal) {
            return true;
        }

        return false;
    }

    public function getPaymentsForOrder(OrderInterface $order) // todo add return type
    {
        $payments = $this->paymentCollectionFactory->create()
            ->addFieldToFilter('order_id', $order->getEntityId());

        return $payments->getItems();
    }

    public function canEdit(OrderInterface $order): bool
    {
        $payments = $this->getPaymentsForOrder($order);
        if(count($payments) === 0) {
            return true;
        }

        return false;
    }
}
