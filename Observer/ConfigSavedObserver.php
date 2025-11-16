<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class ConfigSavedObserver implements ObserverInterface
{
    /**
     * @var OpenPosConfiguration;
     */
    protected $openPosConfiguration;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @param OpenPosConfiguration $openPosConfiguration
     * @param WriterInterface $configWriter
     * @param MessageManager $messageManager
     */
    public function __construct(
        OpenPosConfiguration $openPosConfiguration,
        WriterInterface $configWriter,
        MessageManager $messageManager
    ) {
        $this->openPosConfiguration = $openPosConfiguration;
        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
    }

    /**
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $changedPaths = $observer->getEvent()->getData('changed_paths');
        if(in_array( OpenPosConfiguration::CONFIG_PATH_GENERAL_TILL_USERS, $changedPaths)) {
            $this->messageManager->addNoticeMessage(__('OpenPOS till users have been changed. Magento cache may need to be flushed before tills will allow logon.'));
        }

    }
}