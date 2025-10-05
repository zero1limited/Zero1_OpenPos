<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Backend\Model\Auth;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\DataObjectFactory;
use Zero1\OpenPosLayaways\Model\PaymentMethod;
use Magento\Framework\Exception\LocalizedException;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;

class Stash extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param Context $context
     * @param Auth $auth
     * @param OpenPosHelper $openPosHelper
     * @param OpenPosSessionHelper $openPosSessionHelper
     * @param CheckoutSession $checkoutSession
     * @param QuoteManagement $quoteManagement
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Context $context,
        Auth $auth,
        OpenPosHelper $openPosHelper,
        OpenPosSessionHelper $openPosSessionHelper,
        CheckoutSession $checkoutSession,
        QuoteManagement $quoteManagement,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->auth = $auth;
        $this->openPosHelper = $openPosHelper;
        $this->openPosSessionHelper = $openPosSessionHelper;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context);
    }

    /**
     * Create the current quote as an order pending payment
     * 
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if(!$this->openPosHelper->currentlyOnPosStore() || !$this->openPosSessionHelper->isTillSessionActive()) {
            // TODO harden maybe 404?
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        if($this->getRequest()->isPost()) {
            try {
                // Get current quote
                $quote = $this->checkoutSession->getQuote();
                $quote->getPayment()->importData([
                    'method' => 'openpos_layaways' // todo sort
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
