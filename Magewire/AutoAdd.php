<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Framework\DataObject\Factory as ObjectFactory;

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
    public $customPriceInput = 0;

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
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        PosHelper $posHelper,
        ObjectFactory $objectFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->posHelper = $posHelper;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Add product to the cart from the skuInput.
     *
     * @return void
     */
    public function parseSkuInput()
    {
        if($this->skuInput === ''){
            return;
        }

        if(!$this->customProductMode && $this->skuInput == $this->posHelper->getCustomProductBarcode() && $this->posHelper->getCustomProductBarcode() != '') {
            $this->priceEditorMode = true;
            $this->customProductMode = true;
            $this->showSkuField = false;
            $this->dispatchNoticeMessage('Custom product mode has been enabled.');
            return;
        }

        if($this->skuInput == $this->posHelper->getPriceEditorBarcode() && $this->posHelper->getPriceEditorBarcode() != '') {
            $this->priceEditorMode = true;
            $this->skuInput = '';
            $this->dispatchNoticeMessage('Price editor mode has been enabled.');
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
                $this->redirect('/catalogsearch/result/?q=' . $this->skuInput . '&append=');
                return;
            }
        }

        if($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $this->redirect('/catalogsearch/result/?q='.$this->skuInput);
            //$this->dispatchErrorMessage('The SKU you are trying to add isn\'t a simple product, so cannot be added to the cart.');
            return;
        }

        try {
            $item = $this->addProductToQuote($product);
            $this->redirect('/checkout/cart/index/');
        } catch(\Exception $e) {
            $this->dispatchErrorMessage('There was a problem adding this product to the cart.');
            return;
        }

        $this->dispatchSuccessMessage($this->skuInput.' has been added to cart.');
    }

    public function addProductToQuote($product)
    {
        try {
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

            if($this->priceEditorMode) {
                $this->customPriceInput = (float)$this->customPriceInput;
                $item->setCustomPrice($this->customPriceInput);
                $item->setOriginalCustomPrice($this->customPriceInput);
                $item->getProduct()->setIsSuperMode(true);
            }
            $quote->collectTotals()->save();

            return $item;
        } catch(\Exception $e) {
            $this->dispatchErrorMessage('There was a problem adding this product to the cart.');
        }
    }

}
