<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Model\Config\Backend;

use Magento\Framework\App\Cache\Type\Block as BlockCache;
use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\Config\Value;

/**
 * For config deciding which modules' layout loads on the POS store (Zero1\OpenPos\Model\Layout\ModuleIntegrationFilter).
 * Merged layout is cached, so flag the layout and block caches for a refresh when it changes.
 */
class InvalidateLayoutCache extends Value
{
    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->cacheTypeList->invalidate([LayoutCache::TYPE_IDENTIFIER, BlockCache::TYPE_IDENTIFIER]);
        }

        return parent::afterSave();
    }
}
