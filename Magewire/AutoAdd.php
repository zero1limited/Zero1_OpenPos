<?php
declare(strict_types=1);

namespace Zero1\Pos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

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
     * @var string
     */
    public $skuInput = '';

    /**
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
    }

    /**
     * Add product to the cart from the skuInput.
     *
     * @return void
     */
    public function addProduct(): void
    {
        if($this->skuInput===''){
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
            $quote->addProduct($product, 1);
            $quote->collectTotals()->save();
            $this->redirect('/checkout/cart/index/');
        } catch(\Exception $e) {
            $this->dispatchErrorMessage('There was a problem adding this product to the cart.');
            return;
        }

        $this->dispatchSuccessMessage($this->skuInput.' has been added to cart.');
    }
}
