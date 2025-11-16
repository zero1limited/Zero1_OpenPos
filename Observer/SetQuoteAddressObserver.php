<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\User\Model\User;

class SetQuoteAddressObserver implements ObserverInterface
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param TillSessionManagement $tillSessionManagement
     * @param CustomerSession $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $region
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        TillSessionManagement $tillSessionManagement,
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->tillSessionManagement = $tillSessionManagement;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Check if module is enabled, and we are on the POS store frontend
        if(!$this->openPosConfiguration->isEnabled() || $this->tillSessionManagement->isAdminSession() || !$this->tillSessionManagement->isTillSessionActive()) {
            return;
        }

        $quote = $observer->getData('quote');

        // Only modify valid quotes
        if(!$quote->getId()) {
            return;
        }

        $adminUser = $this->tillSessionManagement->getAdminUserFromTillSession();
        $quote->setCustomerEmail($adminUser->getEmail());
        $this->setDefaultAddress($quote->getShippingAddress(), $adminUser);

        try {
            // Attempt to set customer default billing address
            if($this->customerSession->isLoggedIn() && !$this->openPosConfiguration->getForceStoreBillingAddress()) {
                $defaultBillingAddress = $this->customerSession->getCustomer()->getDefaultBillingAddress();
                if($defaultBillingAddress && $defaultBillingAddress->getId()) {
                    $quote->getBillingAddress()->addData($defaultBillingAddress->getData());
                }
            } else {
                // Customer is not logged in or OpenPOS configuration doesn't allow customer billing address to be set, replace address with default.
                $this->setDefaultAddress($quote->getBillingAddress(), $adminUser);
            }
        } catch(\Exception $e) {
            // If there is an issue with the address, just set default.
            $this->setDefaultAddress($quote->getBillingAddress(), $adminUser);
        }

        // We only need to set store address if the customers default billing address isn't complete.
        if(!$this->isAddressComplete($quote->getBillingAddress())) {
            $this->setDefaultAddress($quote->getBillingAddress(), $adminUser);
        }
    }

    /**
     * @param Address $address
     * @param User $adminUser
     */
    protected function setDefaultAddress(Address $address, User $adminUser): void
    {
        $address->setEmail($adminUser->getEmail());
        $address->setFirstname($adminUser->getFirstName());
        $address->setLastname($adminUser->getLastName());
        $address->setCustomerAddressId(null);
        $address->setSaveInAddressBook(0);

        // Logic to handle either dropdown / selectable regions or just text based region entry.
        $regionId = null;
        $regionName = null;
        $region = $this->regionFactory->create()->load($this->scopeConfig->getValue('general/store_information/region_id'));
        if($region->getId()) {
            // This is a dropdown region (US etc)
            $regionId = $region->getId();
            $regionName = $region->getName();
        } else {
            // Text based entry (UK etc)
            $regionName = $this->scopeConfig->getValue('general/store_information/region_id');
        }

        $address->setStreet([
            $this->scopeConfig->getValue('general/store_information/street_line1') ?? 'OpenPOS placeholder street',
            $this->scopeConfig->getValue('general/store_information/street_line2') ?? ''
        ]);
        $address->setCity($this->scopeConfig->getValue('general/store_information/city') ?? 'OpenPOS placeholder city');
        $address->setRegionId($regionId);
        $address->setRegion($regionName ?? 'OpenPOS placeholder region');
        $address->setPostcode($this->scopeConfig->getValue('general/store_information/postcode') ?? 'OpenPOS placeholder postcode');
        $address->setCountryId($this->scopeConfig->getValue('general/store_information/country_id') ?? 'GB');
        $address->setTelephone($this->scopeConfig->getValue('general/store_information/phone') ?? '123456789');
    }

     /**
     * Check if quote address is complete
     *
     * @param Address $address
     * @return bool
     */
    public function isAddressComplete(Address $address): bool
    {
        return !(
            !$address->getFirstname() ||
            !$address->getLastname() ||
            !$address->getStreet() ||
            !$address->getCity() ||
            (!$address->getRegionId() && !$address->getRegion()) ||
            !$address->getPostcode() ||
            !$address->getCountryId() ||
            !$address->getTelephone()
        );
    }
}
