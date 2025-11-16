<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Magento\Framework\Event\Observer;
use Zero1\OpenPos\Model\OrderManagement;
use Magento\Quote\Observer\SubmitObserver;

class DisableOrderEmails
{
    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @param OrderManagement $openPosConfiguration
     */
    public function __construct(
        OrderManagement $orderManagement
    ) {
        $this->orderManagement = $orderManagement;
    }

    /**
     * Disable order emails for OpenPOS orders
     *
     * @param SubmitObserver $subject
     * @param Observer $observer
     * @return Observer[]
     */
    public function beforeExecute(SubmitObserver $subject, Observer $observer): array
    {
        $order = $observer->getEvent()->getOrder();

        if($this->orderManagement->isPosOrder($order)) {
            $order->setCanSendNewEmailFlag(false);
        }

        return [$observer];
    }
}
