<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Customer\Model\Session as CustomerSession;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as CustomerAddressCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerManagement extends Component
{
    public $listeners = ['changeToCustomerById'];

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var CustomerAddressCollectionFactory
     */
    protected $customerAddressCollectionFactory;

    /**
     * @var string
     */
    public $inputSearch = '';

    /**
     * @var array
     */
    public $customerSearchResults = [];

    /**
     * @param CustomerSession $customerSession
     * @param OpenPosSessionHelper $openPosSessionHelper
     * @param CustomerRepository $customerRepository
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerAddressCollectionFactory $customerAddressCollectionFactory
     */
    public function __construct(
        CustomerSession $customerSession,
        OpenPosSessionHelper $openPosSessionHelper,
        CustomerRepositoryInterface $customerRepository,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerAddressCollectionFactory $customerAddressCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->openPosSessionHelper = $openPosSessionHelper;
        $this->customerRepository = $customerRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerAddressCollectionFactory = $customerAddressCollectionFactory;
    }

    /**
     * @return string
     */
    public function getCurrentCustomerName(): string
    {
        return $this->customerSession->getCustomer()->getName();
    }

    /**
     * @return string
     */
    public function getCurrentCustomerEmail(): string
    {
        return $this->customerSession->getCustomer()->getEmail();
    }

    /**
     * Check if we are currently serving guest.
     * 
     * @return bool
     */
    public function isCurrentCustomerGuest(): bool
    {
        return $this->customerSession->isLoggedIn() === false;
    }

    /**
     * Switch to guest.
     * 
     * @return void
     */
    public function changeToGuest(): void
    {        
        $this->customerSession->setCustomerId(null);
        $this->redirect('/');
    }

    /**
     * Switch to customer using provided ID.
     * 
     * @return void
     */
    public function changeToCustomerById(int $id): void
    {
        if(!$this->openPosSessionHelper->getTillSession()) {
            $this->redirect('/');
            return;
        }
        
        try {
            $customer = $this->customerRepository->getById($id);
        } catch(NoSuchEntityException $e) {
            $this->dispatchErrorMessage(__('Customer cannot be found, cannot switch customer.'));
            return;
        }

        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $this->redirect('/');
    }

    /**
     * Perform search for customers
     * 
     * @return void
     */
    public function updatedInputSearch(): void
    {
        if(!$this->openPosSessionHelper->getTillSession()) {
            $this->redirect('/');
            return;
        }

        $this->customerSearchResults = [];
        $searchValue = $this->inputSearch;

        // Search email, firstname, lastname
        $customers = $this->customerCollectionFactory->create();
        $customers->addAttributeToSelect('*');
        $customers->addAttributeToFilter(
            [
                ['attribute' => 'email', 'like' => "%$searchValue%"],
                ['attribute' => 'firstname', 'like' => "%$searchValue%"],
                ['attribute' => 'lastname', 'like' => "%$searchValue%"],
            ]
        );

        foreach($customers as $customer) {
            $this->customerSearchResults[$customer->getId()] = [
                'id' => $customer->getId(),
                'name' => $customer->getFirstname().' '.$customer->getLastname(),
                'email' => $customer->getEmail(),
            ];
        }

        // Search phone number
        $addressCollection = $this->customerAddressCollectionFactory->create();
        $addressCollection->addAttributeToFilter(
            [
                ['attribute' => 'telephone', 'like' => "%$this->inputSearch%"]
            ]
        );

        foreach($addressCollection as $address) {
            $customerId = $address->getCustomerId();
            if(!isset($this->customerSearchResults[$customerId])) {
                $customer = $this->customerRepository->getById($customerId);
                $this->customerSearchResults[] = [
                    'id' => $customer->getId(),
                    'name' => $customer->getFirstname().' '.$customer->getLastname(),
                    'email' => $customer->getEmail(),
                ];
            }
        }
    }
}
