<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Console\Command;

use Symfony\Component\Console\Command\Command;
use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\Data\GroupInterfaceFactory;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\User\Model\ResourceModel\User\CollectionFactory as AdminUserCollectionFactory;
use Magento\User\Model\UserFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Magento\Framework\Exception\NoSuchEntityException;

class SetupWizard extends Command
{
    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @var WebsiteInterfaceFactory
     */
    protected $websiteFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var GroupInterfaceFactory
     */
    protected $groupFactory;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;
    
    /**
     * @var StoreInterfaceFactory
     */
    protected $storeFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ThemeCollectionFactory
     */
    protected $themeCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AdminUserCollectionFactory
     */
    protected $adminUserCollectionFactory;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @param OpenPosHelper $openPosHelper
     * @param WebsiteInterfaceFactory $websiteFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupInterfaceFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param StoreInterfaceFactory $storeFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param WriterInterface $configWriter
     * @param ThemeCollectionFactory $themeCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param AdminUserCollectionFactory $adminUserCollectionFactory
     * @param UserFactory $userFactory
     */    
    public function __construct(
        OpenPosHelper $openPosHelper,
        WebsiteInterfaceFactory $websiteFactory,
        WebsiteRepositoryInterface $websiteRepository,
        GroupInterfaceFactory $groupFactory,
        GroupRepositoryInterface $groupRepository,
        StoreInterfaceFactory $storeFactory,
        StoreRepositoryInterface $storeRepository,
        WriterInterface $configWriter,
        ThemeCollectionFactory $themeCollectionFactory,
        StoreManagerInterface $storeManager,
        AdminUserCollectionFactory $adminUserCollectionFactory,
        UserFactory $userFactory
    ) {
        $this->openPosHelper = $openPosHelper;
        $this->websiteFactory = $websiteFactory;
        $this->websiteRepository = $websiteRepository;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->storeFactory = $storeFactory;
        $this->storeRepository = $storeRepository;
        $this->configWriter = $configWriter;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->storeManager = $storeManager;
        $this->adminUserCollectionFactory = $adminUserCollectionFactory;
        $this->userFactory = $userFactory;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('openpos:setup-wizard');
        $this->setDescription('Begin OpenPOS setup.');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $output->write(sprintf("\033\143"));
        $output->writeln('<info>Welcome to OpenPOS.</info>');
        $output->writeln('<comment>This command will help you setup OpenPOS by creating a store and writing the necessary configuration.</comment>');

        $question = new ConfirmationQuestion('Would you like to start? [y/n] ', false);
        if(!$helper->ask($input, $output, $question)) {
            return 0;
        }

        if($this->openPosHelper->getIsConfigured()) {
            $output->writeln('<error>It looks like you have already configured OpenPOS. Continuing with the setup wizard may erase data and configuration.</error>');
            $output->writeln('<fg=black;bg=cyan>It\'s not advisable to continue running this command on production environments without testing on staging first.</>');
            $question = new ConfirmationQuestion('Would you like to continue anyway? [y/n] ', false);
            if(!$helper->ask($input, $output, $question)) {
                return 0;
            }
        }

        $output->writeln('<comment>OpenPOS must have it\'s own Magento store to run from.</comment>');
        $question = new ConfirmationQuestion('Is there an existing Magento store you would like to use? [y/n] ', false);
        $existingStore = $helper->ask($input, $output, $question);

        if($existingStore) {
            // List all stores, ask which one to use
            $stores = $this->storeManager->getStores();
            foreach($stores as $store) {
                $storeCodes[] = $store->getCode();
            }

            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select the store you would like OpenPOS to run from...',
                $storeCodes
            );

            $storeCode = $helper->ask($input, $output, $question);

            $store = $this->storeRepository->get($storeCode);
            $website = $store->getWebsite();
        } else {
            // Check for existing website / group / store made previously by the setup wizard.
            try {
                $this->websiteRepository->get('openpos');
                $output->writeln('<error>There is an existing website with code: openpos</error>');
                $output->writeln('<error>Please delete this website before continuing.</error>');
                return 1;
            } catch(NoSuchEntityException $e) {}

            try {
                $this->groupRepository->get('openpos');
                $output->writeln('<error>There is an existing store group with code: openpos</error>');
                $output->writeln('<error>Please delete this store group before continuing.</error>');
                return 1;
            } catch(NoSuchEntityException $e) {}

            try {
                $this->storeRepository->get('openpos');
                $output->writeln('<error>There is an existing store with code: openpos</error>');
                $output->writeln('<error>Please delete this store before continuing.</error>');
                return 1;
            } catch(NoSuchEntityException $e) {}

            $website = $this->websiteFactory->create();
            $website->setCode('openpos');
            $website->setName('OpenPOS');
            $website->save();
            
            $group = $this->groupFactory->create();
            $group->setWebsiteId($website->getId());
            $group->setCode('openpos');
            $group->setName('OpenPOS');
            $group->save();

            $store = $this->storeFactory->create();
            $store->setCode('openpos');
            $store->setWebsiteId($website->getId());
            $store->setGroupId($group->getId());
            $store->setName('OpenPOS');
            $store->setIsActive(true);
            $store->save();

            $output->writeln('<info>Website, store group, and store successfully created.</info>');
            // Set store URL
            $question = new Question('What is the URL you would like OpenPOS to be served from? This will be the Magento store base URL. (example: https://pos.zero1.co.uk/): ');
            $baseUrl = $helper->ask($input, $output, $question);

            $this->configWriter->save('web/unsecure/base_url', $baseUrl, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website->getId());
            $this->configWriter->save('web/secure/base_url', $baseUrl, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website->getId());
        }

        // Set OpenPOS store ID
        $this->configWriter->save(OpenPosHelper::CONFIG_PATH_GENERAL_POS_STORE, $store->getId());

        // Set theme
        $themeCollection = $this->themeCollectionFactory->create();
        $themeCollection->addFieldToFilter('theme_path', ['eq' => 'openpos/default']);
        $openPosTheme = $themeCollection->getFirstItem();

        if(!$openPosTheme->getId()) {
            $output->writeln('<error>The OpenPOS default theme is not installed. Ensure setup:upgrade has been ran.</error>');
            return 1;
        }
        $this->configWriter->save('design/theme/theme_id', $openPosTheme->getId(), \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website->getId());

        // Set first till user
        $output->writeln('<comment>To login to OpenPOS, a Magento admin account is used.</comment>');
        $adminUserCollection = $this->adminUserCollectionFactory->create();
        $adminUserCollection->addFieldToFilter('is_active', ['eq' => 1]);

        foreach ($adminUserCollection->getItems() as $adminUser) {
            $adminUsers[] = $adminUser->getUserName();
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select an existing Magento admin user to become the first OpenPOS user... (you can add more users afterwards)',
            $adminUsers
        );

        $adminUser = $helper->ask($input, $output, $question);
        $adminUser = $this->userFactory->create()->loadByUsername($adminUser);
        $this->configWriter->save(OpenPosHelper::CONFIG_PATH_GENERAL_TILL_USERS, $adminUser->getId());

        // Set misc config
        $this->configWriter->save('checkout/options/guest_checkout', 1, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website->getId());
        $this->configWriter->save('checkout/sidebar/display', 1, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website->getId());
        $this->configWriter->save('checkout/cart/redirect_to_cart', 1, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES, $website->getId());

        // Enable OpenPOS
        $this->configWriter->save(OpenPosHelper::CONFIG_PATH_GENERAL_ENABLE, 1);

        $output->writeln('<info>OpenPOS setup is complete.</info>');
        $output->writeln('<info>Use the Magento admin for further configuration.</info>');
        $output->writeln('<comment>Flushing the Magento cache may be required!</comment>');

        if(!$existingStore) {
            $output->writeln('<comment>Since you created a website and store as part of this setup, you may need to make changes to your NGINX / Apache configuration also.</comment>');
        }

        return 0;
    }
}
