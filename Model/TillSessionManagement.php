<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionFactory;
use Zero1\OpenPos\Api\TillSessionRepositoryInterface;
use Zero1\OpenPos\Model\ResourceModel\TillSession\CollectionFactory as TillSessionCollectionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\User\Model\UserFactory;
use Zero1\OpenPos\Api\Data\TillSessionInterface;
use Magento\User\Model\User;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class TillSessionManagement
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionFactory
     */
    protected $tillSessionFactory;

    /**
     * @var TillSessionRepositoryInterface
     */
    protected $tillSessionRepository;

    /**
     * @var TillSessionCollectionFactory
     */
    protected $tillSessionCollectionFactory;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionFactory $tillSessionFactory
     * @param TillSessionRepositoryInterface $tillSessionRepository
     * @param TillSessionCollectionFactory $tillSessionCollectionFactory
     * @param CheckoutSession $checkoutSession
     * @param QuoteManagement $quoteManagement
     * @param CartRepositoryInterface $quoteRepository
     * @param SessionManagerInterface $sessionManager
     * @param StoreManagerInterface $storeManager
     * @param MessageManager $messageManager
     * @param UserFactory $userFactory
     * @param State $appState
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        TillSessionFactory $tillSessionFactory,
        TillSessionRepositoryInterface $tillSessionRepository,
        TillSessionCollectionFactory $tillSessionCollectionFactory,
        CheckoutSession $checkoutSession,
        QuoteManagement $quoteManagement,
        CartRepositoryInterface $quoteRepository,
        SessionManagerInterface $sessionManager,
        StoreManagerInterface $storeManager,
        MessageManager $messageManager,
        UserFactory $userFactory,
        State $appState
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionFactory = $tillSessionFactory;
        $this->tillSessionRepository = $tillSessionRepository;
        $this->tillSessionCollectionFactory = $tillSessionCollectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->sessionManager = $sessionManager;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->userFactory = $userFactory;
        $this->appState = $appState;
    }

    /**
     * Return till session ID
     * 
     * @return int|null
     */
    public function getTillSessionId()
    {
        return $this->sessionManager->getOpenPosTillSessionId();
    }

    /**
     * Set till session ID
     * 
     * @param int $sessionId
     * @return void
     */
    public function setTillSessionId($sessionId): void
    {
        $this->sessionManager->start();
        $this->sessionManager->setOpenPosTillSessionId($sessionId);
    }

    /**
     * Return current till session object
     * 
     * @return TillSessionInterface|null
     */
    public function getTillSession(): ?TillSessionInterface
    {
        $tillSessionId = $this->getTillSessionId();
        try {
            $tillSession = $this->tillSessionRepository->getById($tillSessionId);
        } catch(NoSuchEntityException $e) {
            return null;
        }
        
        return $tillSession;
    }

    /**
     * Check if a till session is active
     * 
     * @param TillSessionInterface|null $tillSession
     * @return bool
     */
    public function isTillSessionActive(TillSessionInterface $tillSession = null): bool
    {
        if($this->openPosConfiguration->isEnabled() !== true || $this->currentlyOnPosStore() == false) {
            $this->destroySession();
            return false;
        }
        
        if(!$tillSession) {
            $tillSession = $this->getTillSession();
        }

        if(!$tillSession) {
            return false;
        }

        $tillSessionLifetime = $this->openPosConfiguration->getSessionLifetime();
        if($tillSessionLifetime !== 0) {
            $currentDateTime = new \DateTime();
            $tillSessionExpiry = new \DateTime($tillSession->getCreatedAt());
            $tillSessionExpiry->modify("$tillSessionLifetime minutes");

            if($currentDateTime > $tillSessionExpiry) {
                return false;
            }
        }

        return true;
    }

    /**
     * Destroy till session, and Magento session.
     * 
     * @return void
     */
    public function destroySession(): void
    {
        if($this->getTillSessionId()) {
            try {
                $this->tillSessionRepository->deleteById($this->getTillSessionId());
            } catch(NoSuchEntityException $e) {
                // Might already be removed - we are getting ID from our session
            }
            $this->setTillSessionId(null);
        }

        $this->sessionManager->destroy();
    }

    /**
     * Start a till session
     * 
     * @param User $adminUser
     * @return TillSessionInterface
     */
    public function startTillSession(User $adminUser): TillSessionInterface
    {
        // Check user is on POS store
        if(!$this->currentlyOnPosStore()) {
            $this->destroySession();
            throw new LocalizedException(__('Cannot create till session on non-POS store.'));
        }
                
        if($this->getTillSessionId()) {
            try {
                $this->tillSessionRepository->deleteById($this->getTillSessionId());
            } catch(NoSuchEntityException $e) {
                // Might already be removed - we are getting ID from our session
            }
            $this->setTillSessionId(null);
        }

        $tillUsers = $this->openPosConfiguration->getTillUsers();
        if(!in_array($adminUser->getId(), $tillUsers)) {
            throw new LocalizedException(__('This admin user doesn\'t have permission to use a till.'));
        }

        // Delete other till sessions for same admin user
        $tillSessionCollection = $this->tillSessionCollectionFactory->create();
        $tillSessionCollection->addFieldToFilter('admin_user', ['eq' => $adminUser->getUserName()]);
        foreach($tillSessionCollection as $existingTillSession) {
            $this->tillSessionRepository->delete($existingTillSession);
        }

        // Check till session can be made with current license
        // TODO: Create a proper system for retrieving and verifying this
        $tillSessionCount = 1;
        $maxTillSessionCount = 1;

        $tillSessionCollection = $this->tillSessionCollectionFactory->create();
        foreach($tillSessionCollection as $existingTillSession) {
            // Ignore inactive / expired till sessions
            if(!$this->isTillSessionActive($existingTillSession)) {
                continue;
            }
            $tillSessionCount++;
            if($tillSessionCount > $maxTillSessionCount) {
                $this->tillSessionRepository->delete($existingTillSession);
                $this->messageManager->addWarningMessage(__('Notice: The maximum amount of concurrent till sessions with your current OpenPOS license is: '.$maxTillSessionCount.'. You have logged out: '.$existingTillSession->getAdminUser()));
            }
        }

        $currentQuote = $this->checkoutSession->getQuote();
        $this->quoteRepository->delete($currentQuote);

        $newQuoteId = $this->quoteManagement->createEmptyCart();
		$newQuote = $this->quoteRepository->get($newQuoteId);
		$newQuote->setIsActive(true);
		$this->quoteRepository->save($newQuote);

        $tillSession = $this->tillSessionFactory->create();
        $tillSession->setIsActive(true);
        $tillSession->setSecondaryDisplayPasscode(rand(1111, 9999));
        $tillSession->setAdminUser($adminUser->getUserName());
        $tillSession->setQuoteId($newQuote->getId());
        $this->tillSessionRepository->save($tillSession);

        $this->setTillSessionId($tillSession->getId());

        return $tillSession;
    }

    /**
     * Return admin user from a till session
     * 
     * @param TillSessionInterface|null $tillSession
     * @return User|null
     */
    public function getAdminUserFromTillSession(TillSessionInterface $tillSession = null): ?User
    {
        if(!$tillSession) {
            $tillSession = $this->getTillSession();
        }

        $adminUser = $this->userFactory->create();
        $adminUser->loadByUsername($tillSession->getAdminUser());

        if($adminUser->getId()) {
            return $adminUser;
        }

        return null;
    }

    /**
     * Check if current session is in Magento Admin area.
     * 
     * @return bool
     */
    public function isAdminSession(): bool
    {
        if ($this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return true;
        }
        return false;
    }

    /**
     * Check if we are currently on the POS store.
     *
     * @return bool
     */
    public function currentlyOnPosStore(): bool
    {
        if(!$this->openPosConfiguration->isEnabled()) {
            return false;
        }
        return $this->storeManager->getStore()->getId() == $this->openPosConfiguration->getPosStoreId();
    }
}
