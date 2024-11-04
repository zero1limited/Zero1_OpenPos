<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Validator\EmailAddress;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerManagement extends Component
{
    public $isVisible = true;

    public $listeners = ['$set'];

    /**
     * @var CustomerSession
     */
    protected $customerSession;

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
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    protected $emailValidator;

    protected $customerRepository;

    /**
     * @var bool
     */
    public $showSkuField = true;

    /**
     * @var string
     */
    public $emailInput = '';

    /**
     * @var string
     */
    public $foundCustomer = null;

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
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param ProductCollectionFactory $productCollectionFactory
     * @param OpenPosSessionHelper $openPosSessionHelper
     * @param ObjectFactory $objectFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        OpenPosSessionHelper $openPosSessionHelper,
        ObjectFactory $objectFactory,
        EmailAddress $emailValidator,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->openPosSessionHelper = $openPosSessionHelper;
        $this->objectFactory = $objectFactory;
        $this->emailValidator = $emailValidator;
        $this->customerRepository = $customerRepository;
    }

    public function getCurrentCustomerName()
    {
        return $this->customerSession->getCustomer()->getName();
    }

    public function getCurrentCustomerEmail()
    {
        return $this->customerSession->getCustomer()->getEmail();
    }

    public function changeToGuest()
    {
        $customer = $this->openPosSessionHelper->getCustomerForAdminUser();
        
        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $this->redirect('/');
        $this->dispatchSuccessMessage('Successfully switched to '.$customer->getEmail());
    }

    public function changeToCustomer()
    {
        if(!$this->emailValidator->isValid($this->emailInput)) {
            $this->dispatchErrorMessage('Email is not valid, cannot switch customer.');
            return;
        }

        try {
            $customer = $this->customerRepository->get($this->emailInput);
        } catch(NoSuchEntityException $e) {
            $this->dispatchErrorMessage('Customer cannot be found, cannot switch customer.');
            return;
        }

        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $this->redirect('/');
        $this->dispatchSuccessMessage('Successfully switched to '.$customer->getEmail());
    }

    public function updatedEmailInput(string $value)
    {
        if($this->emailValidator->isValid($this->emailInput)) {
            try {
                $customer = $this->customerRepository->get($this->emailInput);
                if($customer) {
                    $this->foundCustomer = $customer->getFirstname().' '.$customer->getLastname();
                }
            } catch(NoSuchEntityException $e) {
                // customer doesn't exist
            }
        }

        return $value;
    }


}
