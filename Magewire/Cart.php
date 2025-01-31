<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder as ImageUrlBuilder;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Quote\Model\Quote\Item;

class Cart extends Component
{
    public $listeners = ['removeItem', 'editItem'];

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ImageUrlBuilder
     */
    protected $imageUrlBuilder;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var string
     */
    public $editingItemId = null;

    /**
     * @var string
     */
    public $editingItemName = null;

    /**
     * @var string
     */
    public $inputPrice = null;

    /**
     * @var string
     */
    public $inputQty = null;

    /**
     * @param CheckoutSession $checkoutSession
     * @param PricingHelper $pricingHelper
     * @param CartRepositoryInterface $cartRepository
     * @param ImageUrlBuilder $imageUrlBuilder
     * @param OpenPosSessionHelper $openPosSessionHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        PricingHelper $pricingHelper,
        CartRepositoryInterface $cartRepository,
        ImageUrlBuilder $imageUrlBuilder,
        OpenPosSessionHelper $openPosSessionHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->cartRepository = $cartRepository;
        $this->imageUrlBuilder = $imageUrlBuilder;
        $this->openPosSessionHelper = $openPosSessionHelper;
    }

    /**
     * Return an array of items currently in the cart
     * 
     * @return array
     */
    public function getItems(): array
    {
        $items = [];
        $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();

        foreach($quoteItems as $quoteItem) {
            $items[] = [
                'id' => $quoteItem->getId(),
                'name' => $quoteItem->getName(),
                'price' => $this->pricingHelper->currency($quoteItem->getPrice(), true, false),
                'qty' => (int)$quoteItem->getQty(),
                'image' => $this->imageUrlBuilder->getUrl($quoteItem->getProduct()->getThumbnail(), 'product_page_image_small')
            ];
        }

        return $items;
    }

    /**
     * Return the current subtotal
     * 
     * @return string
     */
    public function getSubtotal(): string
    {
        return $this->pricingHelper->currency($this->checkoutSession->getQuote()->getSubtotal(), true, false);
    }

    /**
     * Remove an item from the cart.
     * 
     * @param int $itemId
     * @return void
     */
    public function removeItem(int $itemId): void
    {
        $this->checkoutSession->getQuote()->removeItem($itemId);
        $this->checkoutSession->getQuote()->collectTotals()->save();
        $this->redirect('/');
    }

    /**
     * Start editing an item in the cart.
     * 
     * @param int $itemId
     * @return void
     */
    public function editItem(int $itemId): void
    {
        $quoteItem = $this->getQuoteItemById($itemId);

        if($quoteItem) {
            $this->editingItemId = $quoteItem->getId();
            $this->editingItemName = $quoteItem->getName();
            $this->inputPrice = $this->pricingHelper->currency($quoteItem->getPrice(), false, false);
            $this->inputQty = (int)$quoteItem->getQty();
        }
    }

    /**
     * Finish editing an item in the cart.
     * 
     * @param int $itemId
     * @return void
     */
    public function saveItem(): void
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteItem = $this->getQuoteItemById($this->editingItemId);

        $quoteItem->setCustomPrice($this->inputPrice);
        $quoteItem->setOriginalCustomPrice($this->inputPrice);
        $quoteItem->getProduct()->setIsSuperMode(true); // Required to override price
        // $quoteItem->setQty($this->inputQty);

        $quote->collectTotals();
        $this->cartRepository->save($quote);
        $this->redirect('/');
    }

    /**
     * Cancel editing an item in the cart.
     * 
     * @param int $itemId
     * @return void
     */
    public function cancelEditItem(): void
    {
        $this->editingItemId = null;
        $this->editingItemName = null;
        $this->inputPrice = null;
        $this->inputQty = null;
        $this->redirect('/');
    }

    /**
     * Get quote item by ID
     * 
     * @return Item|null
     */
    protected function getQuoteItemById($itemId): ?Item
    {
        $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
        foreach($quoteItems as $quoteItem) {
            if($quoteItem->getId() == $itemId) {
                return $quoteItem;
            }
        }

        return null;
    }

    /**
     * Return current till session ID
     * 
     * @return int|null
     */
    public function getTillSessionId()
    {
        return $this->openPosSessionHelper->getTillSessionId();
    }

}
