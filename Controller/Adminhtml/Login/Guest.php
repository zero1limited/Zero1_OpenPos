<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Controller\Adminhtml\Login;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface;
use Magento\LoginAsCustomerApi\Api\SaveAuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\SetLoggedAsCustomerCustomerIdInterface;
use Magento\LoginAsCustomerApi\Api\GenerateAuthenticationSecretInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\ManageStoreCookie;

/**
 * Login as customer action
 * Generate secret key and forward to the storefront action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Guest extends Action
{

    /**
     *
     * THIS CLASS ISNT COMPLETE AND REQUIRES MORE WORK FROM CALLUM.
     *
     * // TODO: tidy up, return errors nicely in admin.
     */


    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomer::login';

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var AuthenticationDataInterfaceFactory
     */
    private $authenticationDataFactory;

    /**
     * @var SaveAuthenticationDataInterface
     */
    private $saveAuthenticationData;

    /**
     * @var DeleteAuthenticationDataForUserInterface
     */
    private $deleteAuthenticationDataForUser;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var PosHelper
     */
    private $posHelper;

    /**
     * @var Share
     */
    private $share;

    /**
     * @var ManageStoreCookie
     */
    private $manageStoreCookie;

    /**
     * @var SetLoggedAsCustomerCustomerIdInterface
     */
    private $setLoggedAsCustomerCustomerId;

    /**
     * @var IsLoginAsCustomerEnabledForCustomerInterface
     */
    private $isLoginAsCustomerEnabled;

    /**
     * @var GenerateAuthenticationSecretInterface
     */
    private $generateAuthenticationSecret;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigInterface $config
     * @param AuthenticationDataInterfaceFactory $authenticationDataFactory
     * @param SaveAuthenticationDataInterface $saveAuthenticationData
     * @param DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
     * @param Url $url
     * @param PosHelper $posHelper
     * @param Share|null $share
     * @param ManageStoreCookie|null $manageStoreCookie
     * @param SetLoggedAsCustomerCustomerIdInterface|null $setLoggedAsCustomerCustomerId
     * @param IsLoginAsCustomerEnabledForCustomerInterface|null $isLoginAsCustomerEnabled
     * @param GenerateAuthenticationSecretInterface|null $generateAuthenticationSecret
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $authSession,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        ConfigInterface $config,
        AuthenticationDataInterfaceFactory $authenticationDataFactory,
        SaveAuthenticationDataInterface $saveAuthenticationData,
        DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser,
        Url $url,
        PosHelper $posHelper,
        ?Share $share = null,
        ?ManageStoreCookie $manageStoreCookie = null,
        ?SetLoggedAsCustomerCustomerIdInterface $setLoggedAsCustomerCustomerId = null,
        ?IsLoginAsCustomerEnabledForCustomerInterface $isLoginAsCustomerEnabled = null,
        ?GenerateAuthenticationSecretInterface $generateAuthenticationSecret = null
    ) {
        parent::__construct($context);

        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->authenticationDataFactory = $authenticationDataFactory;
        $this->saveAuthenticationData = $saveAuthenticationData;
        $this->deleteAuthenticationDataForUser = $deleteAuthenticationDataForUser;
        $this->url = $url;
        $this->posHelper = $posHelper;
        $this->share = $share ?? ObjectManager::getInstance()->get(Share::class);
        $this->manageStoreCookie = $manageStoreCookie ?? ObjectManager::getInstance()->get(ManageStoreCookie::class);
        $this->setLoggedAsCustomerCustomerId = $setLoggedAsCustomerCustomerId
            ?? ObjectManager::getInstance()->get(SetLoggedAsCustomerCustomerIdInterface::class);
        $this->isLoginAsCustomerEnabled = $isLoginAsCustomerEnabled
            ?? ObjectManager::getInstance()->get(IsLoginAsCustomerEnabledForCustomerInterface::class);
        $this->generateAuthenticationSecret = $generateAuthenticationSecret
            ?? ObjectManager::getInstance()->get(GenerateAuthenticationSecretInterface::class);

        // $this->custom
    }

    /**
     * Login as customer
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
        // Check module is enabled
        if(!$this->posHelper->isEnabled()) {
            throw new \Exception('POS system is not enabled!');
        }

        // Check POS store configured
        $posStore = $this->posHelper->getPosStore();
        if(!$posStore) {
            throw new \Exception('POS store is not configured or is disabled!');
        }
        $storeId = (int)$posStore->getId();

        // Check configured walk-in customer exists
        try {
            $customer = $this->customerRepository->get($this->posHelper->getWalkinCustomerEmail());
            $customerId = (int)$customer->getId();
        } catch (NoSuchEntityException $e) {
            throw new \Exception('Configured walk-in customer does not exist!');
        }

        $adminUser = $this->authSession->getUser();
        $userId = (int)$adminUser->getId();

        /** @var AuthenticationDataInterface $authenticationData */
        $authenticationData = $this->authenticationDataFactory->create(
            [
                'customerId' => $customerId,
                'adminId' => $userId,
                'extensionAttributes' => null,
            ]
        );

        $this->deleteAuthenticationDataForUser->execute($userId);
        $this->saveAuthenticationData->execute($authenticationData);
        $this->setLoggedAsCustomerCustomerId->execute($customerId);

        $secret = $this->generateAuthenticationSecret->execute($authenticationData);
        $redirectUrl = $this->getLoginProceedRedirectUrl($secret, $storeId);

        return $this->prepareRedirectResult($redirectUrl);
    }

    /**
     * Get login proceed redirect url
     *
     * @param string $secret
     * @param int $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getLoginProceedRedirectUrl(string $secret, int $storeId): string
    {
        $targetStore = $this->storeManager->getStore($storeId);
        $queryParameters = ['secret' => $secret];
        $redirectUrl = $this->url
            ->setScope($targetStore)
            ->getUrl('loginascustomer/login/index', ['_query' => $queryParameters, '_nosid' => true]);

        $defaultStore = $this->storeManager->getDefaultStoreView();
        if ($targetStore->getBaseUrl() === $defaultStore->getBaseUrl()) {
            $redirectUrl = $this->manageStoreCookie->switch($defaultStore, $targetStore, $redirectUrl);
        }

        return $redirectUrl;
    }

    /**
     * Prepare JSON result
     *
     * @param array $messages
     * @param string|null $redirectUrl
     * @return JsonResult
     */
    private function prepareJsonResult(array $messages)
    {
        // TODO fix this and return errors as messages in admin rather than exceptions.
        /** @var JsonResult $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $jsonResult->setData([
            'messages' => $messages,
        ]);

        return $jsonResult;
    }

    /**
     * Prepare redirect result
     *
     * @param array $messages
     * @param string|null $redirectUrl
     * @return JsonResult
     */
    private function prepareRedirectResult(string $redirectUrl)
    {
        /** @var JsonResult $jsonResult */
        $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $redirectResult->setUrl($redirectUrl);

        return $redirectResult;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zero1_Pos::goto');
    }
}
