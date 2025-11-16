<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Magento\Framework\Url;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\LocalizedException;

class UrlProvider
{
    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param Url $url
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        Url $url
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->url = $url;
    }

    /**
     * Return URL for till / OpenPOS frontend.
     * 
     * @return string
     */
    public function getTillUrl(): string
    {
        if(!$this->openPosConfiguration->isEnabled()) {
            throw new LocalizedException(__('OpenPOS is currently disabled. Check configuration.'));
        }

        $posStore = $this->openPosConfiguration->getPosStore();
        if(!$posStore) {
            throw new LocalizedException(__('OpenPOS configuration is incomplete. Check configuration.'));
        }

        return $this->url->getUrl('openpos/tillsession/login', [
            '_scope' => $posStore->getId()
        ]);
    }

    /**
     * Return URL for viewing order on till / OpenPOS frontend.
     */
    public function getOrderViewUrl(OrderInterface $order): string
    {
        $orderId = $order->getId();
        $posStore = $this->openPosConfiguration->getPosStore();
        if(!$posStore) {
            throw new LocalizedException(__('OpenPOS configuration is incomplete. Check configuration.'));
        }

        return $this->url->getUrl('openpos/order/view/id', [
            '_scope' => $posStore->getId(),
            'id' => $orderId
        ]);
    }
}
