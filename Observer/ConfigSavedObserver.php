<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Helper\Data as PosHelper;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class ConfigSavedObserver implements ObserverInterface
{
    /**
     * @var PosHelper
     */
    protected $posHelper;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @param PosHelper $posHelper
     * @param WriterInterface $configWriter
     * @param MessageManager $messageManager
     */
    public function __construct(
        PosHelper $posHelper,
        WriterInterface $configWriter,
        MessageManager $messageManager
    ) {
        $this->posHelper = $posHelper;
        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $changedPaths = $observer->getEvent()->getData('changed_paths');
        if(in_array(PosHelper::CONFIG_PATH_GENERAL_TILL_USERS, $changedPaths)) {
            $this->messageManager->addNoticeMessage(__('OpenPOS till users have been changed. Magento cache may need to be flushed before tills will allow logon.'));
        }

    }
}