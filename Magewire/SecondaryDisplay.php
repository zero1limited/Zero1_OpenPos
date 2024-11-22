<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Zero1\OpenPos\Api\TillSessionRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * IN DEVELOPMENT
 */
class SecondaryDisplay extends Component
{
    public $listeners = ['fetch'];
    
    public $isAuthenticated = false;
    public $quoteId = null;
    public $adminUser = '';
    public $tillSessionId = '';
    public $passcode = '';
    public $tillData = [];

    /**
     * @var TillSessionRepository
     */
    protected $tillSessionRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param TillSessionRepositoryInterface $tillSessionRepository
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        TillSessionRepositoryInterface $tillSessionRepository,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->tillSessionRepository = $tillSessionRepository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * IN DEVELOPMENT
     * 
     * Login method for secondary display.
     * This will likely change to normal login rather than custom ID & PIN.
     * 
     * @return void
     */
    public function login(): void
    {
        try {
            $tillSession = $this->tillSessionRepository->getById($this->tillSessionId);
        } catch(\Exception $e) {
            $this->dispatchErrorMessage($e->getMessage());
            $this->dispatchErrorMessage(__('Till session doesnt exist'));
            return;
        }

        if($tillSession->getSecondaryDisplayPasscode() !== $this->passcode) {
            $this->dispatchErrorMessage(__('Passcode incorrect'));
            return;
        }

        $this->isAuthenticated = true;
        $this->quoteId = $tillSession->getQuoteId();
        $this->fetch();
    }

    /**
     * IN DEVELOPMENT
     * 
     * Fetch and store data from main till session quote
     * 
     * @return void
     */
    public function fetch(): void
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
