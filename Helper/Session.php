<?php

namespace Zero1\OpenPos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Zero1\OpenPos\Model\TillSessionFactory;
use Zero1\OpenPos\Api\TillSessionRepositoryInterface;
use Zero1\OpenPos\Model\ResourceModel\TillSession\CollectionFactory as TillSessionCollectionFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\User\Model\User;
use Magento\Framework\Encryption\EncryptorInterface;
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
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

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
     * @var User
     */
    protected $user;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param PosHelper $posHelper
     * @param TillSessionFactory $tillSessionFactory
     * @param TillSessionRepositoryInterface $tillSessionRepository
     * @param TillSessionCollectionFactory $tillSessionCollectionFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CheckoutSession $checkoutSession
     * @param QuoteManagement $quoteManagement
     * @param CartRepositoryInterface $quoteRepository
     * @param SessionManagerInterface $sessionManager
     * @param MessageManager $messageManager
     * @param User $user
     * @param EncryptorInterface
     */
    public function __construct(
        Context $context,
        PosHelper $posHelper,
        TillSessionFactory $tillSessionFactory,
        TillSessionRepositoryInterface $tillSessionRepository,
        TillSessionCollectionFactory $tillSessionCollectionFactory,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        CheckoutSession $checkoutSession,
        QuoteManagement $quoteManagement,
        CartRepositoryInterface $quoteRepository,
        SessionManagerInterface $sessionManager,
        MessageManager $messageManager,
        User $user,
        EncryptorInterface $encryptor
    ) {
        $this->posHelper = $posHelper;
        $this->tillSessionFactory = $tillSessionFactory;
        $this->tillSessionRepository = $tillSessionRepository;
        $this->tillSessionCollectionFactory = $tillSessionCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->sessionManager = $sessionManager;
        $this->messageManager = $messageManager;
        $this->user = $user;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * Return till session ID
     * 
     * @return int
     */
    public function getTillSessionId()
    {
        return $this->sessionManager->getOpenPosTillSessionId();
    }

    /**
     * Set till session ID
     * 
     * @return $this
     */
    public function setTillSessionId($sessionId)
    {
        $this->sessionManager->start();
        $this->sessionManager->setOpenPosTillSessionId($sessionId);

        return $this;
    }

    /**
     * Return till session object
     * 
     * @return \Zero1\OpenPos\Api\Data\TillSessionInterface
     */
    public function getTillSession()
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
     * Destroy till session, and Magento session.
     * 
     * @return void
     */
    public function destroySession()
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
     * Start a still session
     * 
     * @return \Zero1\OpenPos\Api\Data\TillSessionInterface
     */
    public function startTillSession($adminUser)
    {
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
            throw new \Exception('This admin user doesn\'t have permission to use a till.');
        }

        // Delete other till sessions for same admin user
        $tillSessionCollection = $this->tillSessionCollectionFactory->create();
        $tillSessionCollection->addFieldToFilter('admin_user', ['eq' => $adminUser->getUserName()]);
        foreach($tillSessionCollection as $existingTillSession) {
            $this->tillSessionRepository->delete($existingTillSession);
            $this->messageManager->addWarningMessage('Notice: you were logged into another till and have been logged out automatically.');
        }

        $currentQuote = $this->checkoutSession->getQuote();
        $this->quoteRepository->delete($currentQuote);

        $customer = $this->getCustomerForAdminUser($adminUser);
		$newQuoteId = $this->quoteManagement->createEmptyCartForCustomer($customer->getId());
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
     * Return guest customer assigned to admin / till user
     * 
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomerForAdminUser($adminUser = null)
    {
        if(!$adminUser) {
            $tillSession = $this->getTillSession();
            $adminUser = $this->user->loadByUsername($tillSession->getAdminUser());
        }

        // TODO build string better
        $customerEmail = 'openpos-'.$adminUser->getUsername().'@'.$this->posHelper->getEmailDomain();

        try {
            $customer = $this->customerRepository->get($customerEmail, $this->posHelper->getPosStore()->getWebsiteId());
        } catch(NoSuchEntityException $e) {
            $customer = $this->createCustomerForAdminUser($adminUser, $customerEmail);
        }

        return $customer;
    }

    /**
     * Create guest customer for admin / till user
     * 
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function createCustomerForAdminUser($adminUser, $email)
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($this->posHelper->getPosStore()->getWebsiteId());

        $customer->setEmail($email);
        $customer->setFirstname($adminUser->getFirstname());
        $customer->setLastname($adminUser->getLastname());

        $password = $this->encryptor->getHash(substr(str_shuffle(MD5(microtime())), 0, 10), true);
        return $this->customerRepository->save($customer, $password);
    }
}
