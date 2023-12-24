<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Zero1\OpenPos\Helper\Data as PosHelper;

class AutoAdd extends Component
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var PosHelper
     */
    protected $posHelper;

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
    public $superMode = false;

    /**
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param PosHelper $posHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        PosHelper $posHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->posHelper = $posHelper;
    }

    /**
     * Add product to the cart from the skuInput.
     *
     * @return void
     */
    public function addProduct(): void
    {
        if($this->skuInput === ''){
            return;
        }

        if($this->skuInput == $this->posHelper->getSuperBarcode() && $this->posHelper->getSuperBarcode() != '') {
            $this->superMode = true;
            $this->skuInput = '';
            $this->dispatchNoticeMessage('Super mode has been enabled.');
            return;
        }

        try {
            $product = $this->productRepository->get($this->skuInput);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            // TODO - need to search on secondary field Ie Barcode/custom field
            $this->redirect('/catalogsearch/result/?q='.$this->skuInput);
            return;
        }

        if($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $this->redirect('/catalogsearch/result/?q='.$this->skuInput);
            //$this->dispatchErrorMessage('The SKU you are trying to add isn\'t a simple product, so cannot be added to the cart.');
            return;
        }

        try {
            $quote = $this->checkoutSession->getQuote();
            $item = $quote->addProduct($product, 1);

            if($this->superMode) {
                $this->customPriceInput = (float)$this->customPriceInput;
                $item->setCustomPrice($this->customPriceInput);
                $item->setOriginalCustomPrice($this->customPriceInput);
                $item->getProduct()->setIsSuperMode(true);
            }
            $quote->collectTotals()->save();
            $this->redirect('/checkout/cart/index/');
        } catch(\Exception $e) {
            $this->dispatchErrorMessage('There was a problem adding this product to the cart.');
            return;
        }

        $this->dispatchSuccessMessage($this->skuInput.' has been added to cart.');
    }
}
