<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * The till home page is served by OpenPOS (Zero1\OpenPos\Controller\Index\Index) rather than the store's CMS home page,
 * so it can't pick up content, page layout or meta data meant for the main storefront.
 */
class TillHomepage
{
    const CONFIG_PATH_DEFAULT_FRONT = 'web/default/front';

    /**
     * Route OpenPOS serves the till home page from
     */
    const FRONT_NAME = 'openpos';

    /**
     * Magento's default for web/default/front, the store's CMS home page
     */
    const MAGENTO_DEFAULT_FRONT = 'cms';

    /**
     * CMS block shown on the till home page, see view/frontend/layout/openpos_index_index.xml
     */
    const CMS_BLOCK_IDENTIFIER = 'openpos-homepage';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var GetBlockByIdentifierInterface
     */
    protected $getBlockByIdentifier;

    /**
     * @var BlockInterfaceFactory
     */
    protected $blockFactory;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param GetBlockByIdentifierInterface $getBlockByIdentifier
     * @param BlockInterfaceFactory $blockFactory
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        GetBlockByIdentifierInterface $getBlockByIdentifier,
        BlockInterfaceFactory $blockFactory,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->getBlockByIdentifier = $getBlockByIdentifier;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * Point the POS website's home page at OpenPOS, and create the till home page CMS block if it doesn't exist.
     *
     * The home page is left alone if it has already been pointed somewhere other than Magento's default.
     *
     * @param StoreInterface $posStore
     * @return void
     */
    public function configure(StoreInterface $posStore): void
    {
        $currentFront = $this->scopeConfig->getValue(
            self::CONFIG_PATH_DEFAULT_FRONT,
            ScopeInterface::SCOPE_STORES,
            $posStore->getId()
        );
        if (!$currentFront || $currentFront === self::MAGENTO_DEFAULT_FRONT) {
            $this->configWriter->save(
                self::CONFIG_PATH_DEFAULT_FRONT,
                self::FRONT_NAME,
                ScopeInterface::SCOPE_WEBSITES,
                $posStore->getWebsiteId()
            );
        }

        try {
            $this->getBlockByIdentifier->execute(self::CMS_BLOCK_IDENTIFIER, (int)$posStore->getId());
        } catch (NoSuchEntityException $e) {
            $block = $this->blockFactory->create();
            $block->setIdentifier(self::CMS_BLOCK_IDENTIFIER)
                ->setTitle('OpenPOS homepage')
                ->setContent('')
                ->setIsActive(true)
                ->setStoreId([(int)$posStore->getId()]);
            $this->blockRepository->save($block);
        }
    }
}
