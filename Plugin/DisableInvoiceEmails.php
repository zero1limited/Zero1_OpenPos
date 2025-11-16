<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Magento\Framework\Event\Observer;
use Zero1\OpenPos\Model\OrderManagement as OrderManagement;
use Magento\Quote\Observer\SendInvoiceEmailObserver;

class DisableInvoiceEmails
{
    /**
     * @var OrderManagement
     */
    protected $orderManamement;

    /**
     * @param OrderManagement $orderManamement
     */
    public function __construct(
        OrderManagement $orderManamement
    ) {
        $this->orderManamement = $orderManamement;
    }

    /**
     * Disable invoice emails for OpenPOS orders
     *
     * @param SendInvoiceEmailObserver $subject
     * @param Observer $observer
     * @return Observer[]
     */
    public function beforeExecute(SendInvoiceEmailObserver $subject, Observer $observer): array
    {
        $order = $observer->getEvent()->getOrder();

        if($this->orderManamement->isPosOrder($order)) {
            $order->setCanSendNewEmailFlag(false);
        }

        return [$observer];
    }
}
