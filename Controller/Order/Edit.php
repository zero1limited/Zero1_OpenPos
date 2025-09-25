<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Zero1\OpenPos\Helper\Order as OpenPosOrderHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\Reorder\OrderInfoBuyRequestGetter;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Phrase;

class Edit extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var OpenPosOrderHelper
     */
    protected $openPosOrderHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var OrderInfoBuyRequestGetter
     */
    protected $orderInfoBuyRequestGetter;

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
     * @param OpenPosSessionHelper $openPosSessionHelper
     * @param OpenPosOrderHelper $openPosOrderHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param OrderInfoBuyRequestGetter $orderInfoBuyRequestGetter
     * @param CheckoutSession $checkoutSession
     * @param QuoteManagement $quoteManagement
     */
    public function __construct(
        Context $context,
        OpenPosHelper $openPosHelper,
        OpenPosSessionHelper $openPosSessionHelper,
        OpenPosOrderHelper $openPosOrderHelper,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        OrderInfoBuyRequestGetter $orderInfoBuyRequestGetter,
        CheckoutSession $checkoutSession,
        QuoteManagement $quoteManagement
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->openPosSessionHelper = $openPosSessionHelper;
        $this->openPosOrderHelper = $openPosOrderHelper;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->orderInfoBuyRequestGetter = $orderInfoBuyRequestGetter;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        parent::__construct($context);
    }

    /**
     * Convert order into current OpenPOS quote for editing / re-place.
     * 
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if(!$this->openPosHelper->currentlyOnPosStore() || !$this->openPosSessionHelper->isTillSessionActive()) {
            // @todo harden maybe 404?
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        if(!$this->getRequest()->isPost()) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        $orderId = (int)$this->getRequest()->getParam('id');
        if (!$orderId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        $order = $this->orderRepository->get($orderId);

        // Check if the order has already been paid
        if(!$this->openPosOrderHelper->canEdit($order)) {
            $this->messageManager->addErrorMessage(__('A payment has already been made against this order, it can no longer be edited.'));
            $resultRedirect->setPath('openpos/order/view', ['id' => $orderId]);
            return $resultRedirect;
        }

        // Delete current quote and create a new one
        $currentQuote = $this->checkoutSession->getQuote();
        $this->quoteRepository->delete($currentQuote);

        $newQuoteId = $this->quoteManagement->createEmptyCart();
		$newQuote = $this->quoteRepository->get($newQuoteId);
		$newQuote->setIsActive(true);

        // Copy customer ID from order to quote if set
        if($order->getCustomerId()) {
            $newQuote->setCustomerId($order->getCustomerId());
            $newQuote->setCustomerIsGuest(false);
            $newQuote->setCustomerEmail($order->getCustomerEmail());
        } else {
            $newQuote->setCustomerIsGuest(true);
            $newQuote->setCustomerEmail($order->getCustomerEmail());
        }

        // Copy items from order to quote
        $itemsToAdd = [];
        foreach ($order->getItemsCollection() as $orderItem) {
            if ($orderItem->getParentItem() === null) {

                try {
                    $product = $this->productRepository->getById($orderItem->getProductId());
                } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
                    continue;
                }

                $itemsToAdd[] = [
                    'buyRequest' => $this->orderInfoBuyRequestGetter->getInfoBuyRequest($orderItem),
                    'product' => $product
                ];
            }
        }

        if(count($itemsToAdd) === 0) {
            $this->messageManager->addErrorMessage(__('No products could be added to the quote from this order.'));
            $resultRedirect->setPath('openpos/order/view', ['id' => $orderId]);
            return $resultRedirect;
        }

        foreach($itemsToAdd as $itemToAdd) {
            try {
                $newQuote->addProduct($itemToAdd['product'], $itemToAdd['buyRequest']);
            } catch(\Exception $e) {
                $this->messageManager->addErrorMessage(__('Product "%1" could not be added to the quote.', $itemToAdd['product']->getName()));
            }
        }

        // Set current quote
        $this->quoteRepository->save($newQuote);
        $this->checkoutSession->setQuoteId($newQuote->getId());

        // Cancel the old order
        $order->cancel();
        $this->orderRepository->save($order);

        // @todo: add comments to the old order showing edit / unstash time and admin user.

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
