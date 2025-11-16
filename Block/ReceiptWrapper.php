<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Zero1\OpenPos\Model\UrlProvider;
use Magento\Checkout\Model\Session as CheckoutSession;

class ReceiptWrapper extends Template
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;
    
    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var UrlProvider
     */
    protected $urlProvider;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param Context $context
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param UrlProvider $urlProvider
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        UrlProvider $urlProvider,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->urlProvider = $urlProvider;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if(!$this->openPosConfiguration->isEnabled() || !$this->tillSessionManagement->currentlyOnPosStore()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return OpenPOS order view URL
     * 
     * @return string
     */
    public function getOrderViewUrl(): string
    {
        $order = $this->checkoutSession->getLastRealOrder();
        return $this->urlProvider->getOrderViewUrl($order);
    }
}
