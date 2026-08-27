<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Magento\Framework\Url;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\Data\StoreInterface;
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
     * @var BackendHelper
     */
    protected $backendHelper;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param Url $url
     * @param BackendHelper $backendHelper
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        Url $url,
        BackendHelper $backendHelper
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->url = $url;
        $this->backendHelper = $backendHelper;
    }

    /**
     * Return URL for till / OpenPOS frontend.
     *
     * @return string
     */
    public function getTillUrl(): string
    {
        return $this->url->getUrl('openpos/tillsession/login', [
            '_scope' => $this->requirePosStore()->getId()
        ]);
    }

    /**
     * Return URL that logs an admin user straight in to the till using a
     * handoff token, avoiding a second login on the POS domain.
     *
     * @param string $token
     * @return string
     */
    public function getTillHandoffUrl(string $token): string
    {
        return $this->url->getUrl('openpos/tillsession/handoff', [
            '_scope' => $this->requirePosStore()->getId(),
            '_nosid' => true,
            '_query' => ['token' => $token]
        ]);
    }

    /**
     * Return URL for the Magento admin.
     *
     * @return string
     */
    public function getAdminUrl(): string
    {
        return $this->backendHelper->getHomePageUrl();
    }

    /**
     * Return the POS store, failing if OpenPOS isn't ready to be used.
     *
     * @return StoreInterface
     */
    protected function requirePosStore(): StoreInterface
    {
        if(!$this->openPosConfiguration->isEnabled()) {
            throw new LocalizedException(__('OpenPOS is currently disabled. Check configuration.'));
        }

        $posStore = $this->openPosConfiguration->getPosStore();
        if(!$posStore) {
            throw new LocalizedException(__('OpenPOS configuration is incomplete. Check configuration.'));
        }

        return $posStore;
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
