<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class EmulateQuoteShippingAddress
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @param PosHelper $posHelper
     * @param AddressInterfaceFactory $addressFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        PosHelper $posHelper,
        AddressInterfaceFactory $addressFactory,
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory
    ) {
        $this->posHelper = $posHelper;
        $this->addressFactory = $addressFactory;
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param Order $order
     * @param bool $result
     * @return \Magento\Quote\Model\Quote\Address|null
     */
    public function afterGetShippingAddress(Quote $quote, $result)
    {
        // Ensure OpenPOS is enabled and configured to emulate shipping addresses.
        if(!$this->posHelper->isEnabled() || !$this->posHelper->getEmulateShippingAddress()) {
            return $result;
        }

        if($result === null && $this->posHelper->currentlyOnPosStore() && !$this->posHelper->isAdminSession()) {
            $address = $this->addressFactory->create();

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

            $address->setFirstname('foo')
                ->setLastname('bar')
                ->setEmail('callum.breeze@zero1.co.uk')
                ->setStreet([$this->scopeConfig->getValue('general/store_information/street_line1') ?? 'OpenPOS placeholder street', $this->scopeConfig->getValue('general/store_information/street_line2')])
                ->setCity($this->scopeConfig->getValue('general/store_information/city') ?? 'OpenPOS placeholder city')
                ->setRegionId($regionId)
                ->setRegion($regionName ?? 'OpenPOS placeholder region')
                ->setPostcode($this->scopeConfig->getValue('general/store_information/postcode') ?? 'OpenPOS placeholder postcode')
                ->setCountryId($this->scopeConfig->getValue('general/store_information/country_id') ?? 'GB')
                ->setTelephone($this->scopeConfig->getValue('general/store_information/phone') ?? '123456789')
                ->setAddressType(Address::TYPE_SHIPPING);

            return $address;
        }

        return $result;
    }
}
