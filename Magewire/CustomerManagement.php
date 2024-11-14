<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Customer\Model\Session as CustomerSession;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Framework\Validator\EmailAddress;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerManagement extends Component
{
    public $listeners = ['$set'];

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @var EmailAddress
     */
    protected $emailValidator;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var string
     */
    public $emailInput = '';

    /**
     * @var bool
     */
    public $foundCustomer = null;

    /**
     * @param CustomerSession $customerSession
     * @param OpenPosSessionHelper $openPosSessionHelper
     * @param EmailAddress $emailValidator
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        OpenPosSessionHelper $openPosSessionHelper,
        EmailAddress $emailValidator,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerSession = $customerSession;
        $this->openPosSessionHelper = $openPosSessionHelper;
        $this->emailValidator = $emailValidator;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return string
     */
    public function getCurrentCustomerName()
    {
        return $this->customerSession->getCustomer()->getName();
    }

    /**
     * @return string
     */
    public function getCurrentCustomerEmail()
    {
        return $this->customerSession->getCustomer()->getEmail();
    }

    /**
     * Check if we are currently serving guest.
     * 
     * @return bool
     */
    public function isCurrentCustomerGuest()
    {
        $guestCustomer = $this->openPosSessionHelper->getCustomerForAdminUser();
        if($guestCustomer->getEmail() === $this->getCurrentCustomerEmail()) {
            return true;
        }

        return false;
    }

    /**
     * Switch to guest customer.
     * 
     * @return void
     */
    public function changeToGuest()
    {
        $customer = $this->openPosSessionHelper->getCustomerForAdminUser();
        
        $this->customerSession->setCustomerDataAsLoggedIn($customer);
        $this->redirect('/');
        $this->dispatchSuccessMessage('Successfully switched to guest customer.');
    }

    /**
     * Attempt switch to customer using provided email.
     * 
     * @return void
     */
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

    /**
     * Obtain customer details before switch
     * 
     * @return string
     */
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