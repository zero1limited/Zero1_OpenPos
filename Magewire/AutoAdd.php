<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product;

class AutoAdd extends Component
{
    public $isVisible = true;

    public $listeners = ['$set'];

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var bool
     */
    public $showSkuField = true;

    /**
     * @var string
     */
    public $skuInput = '';

    /**
     * @var string
     */
    public $customPriceInput = '';

    /**
     * @var bool
     */
    public $priceEditorMode = false;

    /**
     * @var bool
     */
    public $customProductMode = false;

    /**
     * @var string
     */
    public $descriptionInput = '';

    /**
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param ProductCollectionFactory $productCollectionFactory
     * @param PosHelper $posHelper
     * @param ObjectFactory $objectFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        PosHelper $posHelper,
        ObjectFactory $objectFactory,
        UrlInterface $urlBuilder
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->posHelper = $posHelper;
        $this->objectFactory = $objectFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Add product to the cart from the skuInput.
     *
     * @return void
     */
    public function parseSkuInput(): void
    {
        if($this->skuInput === ''){
            return;
        }

        if(!$this->customProductMode && $this->skuInput == $this->posHelper->getCustomProductBarcode() && $this->posHelper->getCustomProductBarcode() != '') {
            $this->priceEditorMode = false;
            $this->customProductMode = true;
            $this->showSkuField = false;
            return;
        }

        if($this->skuInput == $this->posHelper->getPriceEditorBarcode() && $this->posHelper->getPriceEditorBarcode() != '') {
            $this->customProductMode = false;
            $this->priceEditorMode = true;
            $this->skuInput = '';
            return;
        }

        try {
            $product = null;
            $product = $this->productRepository->get($this->skuInput);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Cannot find product by SKU, so use barcode attribute
            $barcodeAttribute = $this->posHelper->getBarcodeAttribute();
            if($barcodeAttribute) {
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToFilter($barcodeAttribute, ['eq' => $this->skuInput]);
                $productId = $productCollection->getFirstItem()->getId();
                if($productId) {
                    $product = $this->productRepository->getById($productId);
                }
            }

            if(!$product) {
                $this->redirect('/catalogsearch/result/?q=' . $this->skuInput);
                return;
            }
        }

        if($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $this->redirect('/catalogsearch/result/?q='.$this->skuInput);
            return;
        }

        try {
            $item = $this->addProductToQuote($product);
            $this->redirect('/');
        } catch(\Exception $e) {
            $this->dispatchErrorMessage(__('There was a problem adding this product to the cart. Redirected to product page.'));
            $url = $this->urlBuilder->getUrl('catalog/product/view', ['id' => $product->getId()]);
            $this->redirect($url);

            return;
        }
    }

    /**
     * Add a product to the quote
     * @param Product $product
     * @return Item|null
     */
    public function addProductToQuote(Product $product): ?Item
    {
        $quote = $this->checkoutSession->getQuote();
        $request = $this->objectFactory->create(['qty' => 1]);

        if($this->customProductMode) {
            foreach($product->getOptions() as $option) {
                if(strtolower($option->getTitle()) == 'description') {
                    $request->setData('options', [$option->getId() => $this->descriptionInput]);
                }
            }
        }
        $item = $quote->addProduct($product, $request);

        if(!$item instanceof Item) {
            throw new \Exception();
        }

        if($this->priceEditorMode || $this->customProductMode) {
            $this->customPriceInput = (float)$this->customPriceInput;
            $item->setCustomPrice($this->customPriceInput);
            $item->setOriginalCustomPrice($this->customPriceInput);
            $item->getProduct()->setIsSuperMode(true);
        }
        $quote->collectTotals()->save();

        return $item;
    }

    /**
     * Reset whole block / SKU input
     * 
     * @return void
     */
    public function resetInput(): void
    {
        $this->reset();
    }
}
