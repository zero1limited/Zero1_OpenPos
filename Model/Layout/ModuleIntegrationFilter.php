<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\Layout;

use Magento\Framework\App\Area;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Zero1\OpenPos\Model\ModuleIntegration;

/**
 * Leaves out layout and page_layout XML files belonging to modules not allowed by OpenPOS module integration, when
 * layout is built for the POS store. Works like core's Magento\Framework\View\File\Collector\Decorator\ModuleOutput,
 * so those modules' layout instructions (remove, move, arguments, templates, head assets, handle updates) never reach
 * the till and their blocks are never created.
 *
 * Only wraps the file sources layout merging reads (see etc/di.xml), not every collector using ModuleOutput, as other
 * file types (e.g. UI components) are not cached per store.
 *
 * Merged layout is cached per area, store and theme (Magento\Framework\View\Model\Layout\Merge::generateCacheId()), so
 * the decision here must only depend on those. As a result it applies to anything rendered in the POS store's frontend
 * context, including emails and PDFs generated under emulation of the POS store. It is declared in global DI as that
 * emulation can start from any area.
 */
class ModuleIntegrationFilter implements CollectorInterface
{
    /**
     * @var CollectorInterface
     */
    protected $subject;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var ModuleIntegration
     */
    protected $moduleIntegration;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param CollectorInterface $subject
     * @param OpenPosConfiguration $openPosConfiguration
     * @param ModuleIntegration $moduleIntegration
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectorInterface $subject,
        OpenPosConfiguration $openPosConfiguration,
        ModuleIntegration $moduleIntegration,
        StoreManagerInterface $storeManager
    ) {
        $this->subject = $subject;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->moduleIntegration = $moduleIntegration;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $files = $this->subject->getFiles($theme, $filePath);
        if (!$this->isPosStoreLayout($theme)) {
            return $files;
        }

        $allowedModules = array_flip($this->moduleIntegration->getAllowedModules());
        $result = [];
        foreach ($files as $file) {
            $module = $file->getModule();
            if (!$module || isset($allowedModules[$module])) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * Whether layout is being built for the POS store frontend
     *
     * Checked here rather than with TillSessionManagement::currentlyOnPosStore(), as that would bring sessions into
     * every layout merge, including admin and cron.
     *
     * @param ThemeInterface $theme
     * @return bool
     */
    protected function isPosStoreLayout(ThemeInterface $theme): bool
    {
        if ($theme->getArea() !== Area::AREA_FRONTEND || !$this->openPosConfiguration->isEnabled()) {
            return false;
        }

        $posStoreId = $this->openPosConfiguration->getPosStoreId();
        return $posStoreId && (int)$this->storeManager->getStore()->getId() === $posStoreId;
    }
}
