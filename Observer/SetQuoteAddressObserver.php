<?php
namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Quote\Model\Quote\AddressFactory;

class SetQuoteAddressObserver implements ObserverInterface
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @param PosHelper $posHelper
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        PosHelper $posHelper,
        AddressFactory $addressFactory
    ) {
        $this->posHelper = $posHelper;
        $this->addressFactory = $addressFactory;
    }

    public function execute(Observer $observer)
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

        $this->setDefaultAddress($quote->getShippingAddress());
        $this->setDefaultAddress($quote->getBillingAddress());

        // $address = $quote->getShippingAddress();
        // if($address->getFirstname() !== 'OpenPOS') {
        //     $address = $this->addressFactory->create();
        //     $this->setDefaultAddress($address);
        //     $quote->setShippingAddress($address);
        // }

        // $address = $quote->getBillingAddress();
        // if($address->getFirstname() !== 'OpenPOS') {
        //     $address = $this->addressFactory->create();
        //     $this->setDefaultAddress($address);
        //     $quote->setBillingAddress($address);
        // }
    }

    protected function setDefaultAddress($address)
    {
        $address->setFirstname('OpenPOS');
        $address->setLastname('Customer');
        $address->setStreet(['openpos', 'openpos']);
        $address->setCity('openpos');
        $address->setPostcode('openpos');
        $address->setCountryId('GB');
        $address->setTelephone('123456789');
        $address->save();
    }
}
