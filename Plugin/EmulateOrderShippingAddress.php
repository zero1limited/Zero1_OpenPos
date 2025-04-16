<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Plugin;

use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;

class EmulateOrderShippingAddress
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var OrderAddressInterfaceFactory
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
     * @param OrderAddressInterfaceFactory $addressFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        PosHelper $posHelper,
        OrderAddressInterfaceFactory $addressFactory,
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
     * @return \Magento\Sales\Model\Order\Address|null
     */
    public function afterGetShippingAddress(Order $order, $result)
    {
        // Ensure OpenPOS is enabled and configured to emulate shipping addresses.
        if(!$this->posHelper->isEnabled() || !$this->posHelper->getEmulateShippingAddress()) {
            return $result;
        }

        if($result === null && $this->posHelper->isPosOrder($order)) {
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

            $address->setFirstname($order->getCustomerFirstname())
                ->setLastname($order->getCustomerLastname())
                ->setEmail($order->getCustomerEmail())
                ->setStreet([
                    $this->scopeConfig->getValue('general/store_information/street_line1') ?? 'OpenPOS placeholder street',
                    $this->scopeConfig->getValue('general/store_information/street_line2') ?? ''
                ])
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
