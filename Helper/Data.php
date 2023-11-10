<?php
namespace Zero1\Pos\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Zero1\Pos\Helper\Config as ConfigHelper;

class Data extends AbstractHelper
{

    /**
     *
     * We might be able to merge this helper into config helper or vice-versa.
     * Not decided yet - depends what else we need to do in here.
     * // TODO: Remove this / merge if not required.
     *
     * Callum
     */

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ConfigHelper $configHelper
    ) {
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }


    /**
     * @return mixed
     */
    public function getPosStore()
    {
        try {
            $storeId = $this->configHelper->getPosStore();
            return $this->storeManager->getStore($storeId);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getRedirectStore()
    {
        try {
            $storeId = $this->configHelper->getRedirectStore();
            return $this->storeManager->getStore($storeId);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
