<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder as ImageUrlBuilder;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Helper\Product\Configuration as ProductConfigurationHelper;

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
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var ProductConfigurationHelper
     */
    protected $productConfigurationHelper;

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
     * @param TillSessionManagement $tillSessionManagement
     * @param ProductConfigurationHelper $productConfigurationHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        PricingHelper $pricingHelper,
        CartRepositoryInterface $cartRepository,
        ImageUrlBuilder $imageUrlBuilder,
        TillSessionManagement $tillSessionManagement,
        ProductConfigurationHelper $productConfigurationHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->pricingHelper = $pricingHelper;
        $this->cartRepository = $cartRepository;
        $this->imageUrlBuilder = $imageUrlBuilder;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->productConfigurationHelper = $productConfigurationHelper;
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
            $itemOptions = [];
            $options = $this->productConfigurationHelper->getCustomOptions($quoteItem);
            foreach($options as $option) {
                $itemOptions[] = [
                    'label' => $option['label'],
                    'value' => $option['value']
                ];
            }
            $items[] = [
                'id' => $quoteItem->getId(),
                'name' => $quoteItem->getName(),
                'price' => $this->pricingHelper->currency($quoteItem->getPrice(), true, false),
                'qty' => (int)$quoteItem->getQty(),
                'image' => $this->imageUrlBuilder->getUrl((string)$quoteItem->getProduct()->getThumbnail(), 'product_page_image_small'),
                'options' =>  $itemOptions
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
        return $this->tillSessionManagement->getTillSessionId();
    }

}
