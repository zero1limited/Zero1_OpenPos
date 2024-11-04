<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Zero1\OpenPos\Api\TillSessionRepositoryInterface;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Quote\Api\CartRepositoryInterface;

use Magento\Framework\DataObject\Factory as ObjectFactory;

class SecondaryDisplay extends Component
{
    public $isAuthenticated = false;
    public $quoteId = null;

    public $adminUser = '';
    public $tillSessionId = '';
    public $passcode = '';

    public $tillData = [];

    public $listeners = ['fetch'];

    /**
     * @var TillSessionRepository
     */
    protected $tillSessionRepository;

    protected $quoteRepository;

    /**
     * @param TillSessionRepositoryInterface $tillSessionRepository
     */
    public function __construct(
        TillSessionRepositoryInterface $tillSessionRepository,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->tillSessionRepository = $tillSessionRepository;
        $this->quoteRepository = $quoteRepository;
    }

    public function login()
    {
        // if($mage)
        try {
            $tillSession = $this->tillSessionRepository->getById($this->tillSessionId);
        } catch(\Exception $e) {
            $this->dispatchErrorMessage($e->getMessage());
            $this->dispatchErrorMessage('Till session doesnt exist');
            return;
        }

        if($tillSession->getSecondaryDisplayPasscode() !== $this->passcode) {
            $this->dispatchErrorMessage('Passcode incorrect');
            return;
        }

        $this->isAuthenticated = true;
        $this->quoteId = $tillSession->getQuoteId();
        $this->fetch();
    }

    public function fetch()
    {
        $quote = $this->quoteRepository->get($this->quoteId);
        // Get the last visible item
        $quoteItems = $quote->getAllVisibleItems();
        $lastVisibleItem = end($quoteItems);
        $this->tillData = [
            'time' => date('H:i:s'),
            'quote_id' => $quote->getId(),
            'total' => $quote->getGrandTotal(),
            'last_item_name' => $lastVisibleItem ? $lastVisibleItem->getName() : null,
            'last_item_price' => $lastVisibleItem ? $lastVisibleItem->getPrice() : 0.00
        ];
    }

}
