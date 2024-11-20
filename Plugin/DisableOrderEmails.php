<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Magento\Framework\Event\Observer;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\Quote\Observer\SubmitObserver;

class DisableOrderEmails
{
    /**
     * @var OpenPosHelper $openPosHelper
     */
    protected OpenPosHelper $openPosHelper;

    /**
     * @param OpenPosHelper $openPosHelper
     */
    public function __construct(
        OpenPosHelper $openPosHelper
    ) {
        $this->openPosHelper = $openPosHelper;
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

        if($this->openPosHelper->isPosOrder($order)) {
            $order->setCanSendNewEmailFlag(false);
        }

        return [$observer];
    }
}
