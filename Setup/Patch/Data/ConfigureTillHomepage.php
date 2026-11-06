<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Setup\Patch\Data;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\TillHomepage;

/**
 * Moves installs set up before the till home page was served by OpenPOS onto it. New installs get this from the
 * setup wizard.
 */
class ConfigureTillHomepage implements DataPatchInterface
{
    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var TillHomepage
     */
    protected $tillHomepage;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param StoreRepositoryInterface $storeRepository
     * @param TillHomepage $tillHomepage
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        StoreRepositoryInterface $storeRepository,
        TillHomepage $tillHomepage
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->storeRepository = $storeRepository;
        $this->tillHomepage = $tillHomepage;
    }

    /**
     * @return $this
     */
    public function apply()
    {
        $posStoreId = $this->openPosConfiguration->getPosStoreId();
        if (!$this->openPosConfiguration->getIsConfigured() || !$posStoreId) {
            return $this;
        }

        try {
            $posStore = $this->storeRepository->getById($posStoreId);
        } catch (NoSuchEntityException $e) {
            return $this;
        }

        $this->tillHomepage->configure($posStore);
        return $this;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }
}
