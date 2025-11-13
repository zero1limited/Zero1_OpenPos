<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\Context;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Model\Session as OpenPosSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteManagement;
use Zero1\OpenPos\Model\PaymentMethod\Layaways;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Phrase;

class Stash extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosSession
     */
    protected $openPosSession;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @param Context $context
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosSession $openPosSession
     * @param CheckoutSession $checkoutSession
     * @param QuoteManagement $quoteManagement
     */
    public function __construct(
        Context $context,
        OpenPosHelper $openPosHelper,
        OpenPosSession $openPosSession,
        CheckoutSession $checkoutSession,
        QuoteManagement $quoteManagement
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->openPosSession = $openPosSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        parent::__construct($context);
    }

    /**
     * Create the current quote as an order pending payment / stashed order
     * 
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if(!$this->openPosHelper->currentlyOnPosStore() || !$this->openPosSession->isTillSessionActive()) {
            // @todo harden maybe 404?
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        if($this->getRequest()->isPost()) {
            try {
                // Get current quote
                $quote = $this->checkoutSession->getQuote();
                $quote->getPayment()->importData([
                    'method' => Layaways::PAYMENT_METHOD_CODE
                ]);

                $quote->collectTotals();
                $order = $this->quoteManagement->submit($quote);

                if($order && $order->getId()) {
                    $this->messageManager->addSuccessMessage(__('Order #%1 has been placed.', $order->getIncrementId()));
                    $url = $this->openPosHelper->getOrderViewUrl($order);

                    return $resultRedirect->setPath($url);
                } else {
                    $this->messageManager->addErrorMessage(__('Order could not be placed. Please try again.'));
                    return $resultRedirect->setPath('checkout/cart');
                }
            } catch(LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $resultRedirect->setPath('/');
        return $resultRedirect;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('openpos/tillsession/login');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }
}
