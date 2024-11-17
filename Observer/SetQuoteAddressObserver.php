<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Zero1\OpenPos\Helper\Session as OpenPosSessionHelper;
use Magento\Quote\Model\Quote\Address;
use Magento\User\Model\User;

class SetQuoteAddressObserver implements ObserverInterface
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var OpenPosSessionHelper
     */
    protected $openPosSessionHelper;

    /**
     * @param PosHelper $posHelper
     * @param OpenPosSessionHelper $openPosSessionHelper
     */
    public function __construct(
        PosHelper $posHelper,
        OpenPosSessionHelper $openPosSessionHelper
    ) {
        $this->posHelper = $posHelper;
        $this->openPosSessionHelper = $openPosSessionHelper;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Check if module is enabled, and we are on the POS store
        if(!$this->posHelper->isEnabled() || !$this->posHelper->currentlyOnPosStore()) {
            return;
        }

        $quote = $observer->getData('quote');

        // Only modify valid quotes
        if(!$quote->getId()) {
            return;
        }

        $adminUser = $this->openPosSessionHelper->getAdminUserFromTillSession();

        $quote->setCustomerEmail($adminUser->getEmail());
        $this->setDefaultAddress($quote->getShippingAddress(), $adminUser);
        $this->setDefaultAddress($quote->getBillingAddress(), $adminUser);
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

        // TODO: add admin fields for below
        $address->setStreet(['openpos', 'openpos']);
        $address->setCity('openpos');
        $address->setPostcode('openpos');
        $address->setCountryId('GB');
        $address->setTelephone('123456789');
    }
}
