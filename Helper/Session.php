<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Zero1\OpenPos\Model\TillSessionFactory;
use Zero1\OpenPos\Api\TillSessionRepositoryInterface;
use Zero1\OpenPos\Model\ResourceModel\TillSession\CollectionFactory as TillSessionCollectionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\User\Model\UserFactory;
use Zero1\OpenPos\Api\Data\TillSessionInterface;
use Magento\User\Model\User;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Session extends AbstractHelper
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

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
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @param Context $context
     * @param PosHelper $posHelper
     * @param TillSessionFactory $tillSessionFactory
     * @param TillSessionRepositoryInterface $tillSessionRepository
     * @param TillSessionCollectionFactory $tillSessionCollectionFactory
     * @param CheckoutSession $checkoutSession
     * @param QuoteManagement $quoteManagement
     * @param CartRepositoryInterface $quoteRepository
     * @param SessionManagerInterface $sessionManager
     * @param MessageManager $messageManager
     * @param UserFactory $userFactory
     */
    public function __construct(
        Context $context,
        PosHelper $posHelper,
        TillSessionFactory $tillSessionFactory,
        TillSessionRepositoryInterface $tillSessionRepository,
        TillSessionCollectionFactory $tillSessionCollectionFactory,
        CheckoutSession $checkoutSession,
        QuoteManagement $quoteManagement,
        CartRepositoryInterface $quoteRepository,
        SessionManagerInterface $sessionManager,
        MessageManager $messageManager,
        UserFactory $userFactory
    ) {
        $this->posHelper = $posHelper;
        $this->tillSessionFactory = $tillSessionFactory;
        $this->tillSessionRepository = $tillSessionRepository;
        $this->tillSessionCollectionFactory = $tillSessionCollectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->sessionManager = $sessionManager;
        $this->messageManager = $messageManager;
        $this->userFactory = $userFactory;
        parent::__construct($context);
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
        // Check user is on POS store
        if(!$this->posHelper->currentlyOnPosStore()) {
            $this->destroySession();
            return null;
        }

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
        if(!$tillSession) {
            $tillSession = $this->getTillSession();
        }

        if(!$tillSession) {
            return false;
        }

        $tillSessionLifetime = $this->posHelper->getSessionLifetime();
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
        if(!$this->posHelper->currentlyOnPosStore()) {
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

        $tillUsers = $this->posHelper->getTillUsers();
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
}
