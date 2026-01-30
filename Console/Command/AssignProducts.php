<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class AssignProducts extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductAction
     */
    protected $productAction;

    /**
     * @param State $state
     * @param OpenPosConfiguration $openPosConfiguration
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductAction $productAction
     */    
    public function __construct(
        State $state,
        OpenPosConfiguration $openPosConfiguration,
        ProductCollectionFactory $productCollectionFactory,
        ProductAction $productAction
    ) {
        $this->state = $state;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAction = $productAction;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('openpos:assign-products');
        $this->setDescription('Assign all products in the catalog to the OpenPOS website.');

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
        $this->state->setAreaCode('adminhtml');
        $helper = $this->getHelper('question');

        $output->write(sprintf("\033\143"));
        $output->writeln('<comment>WARNING: This command will assign ALL products to the OpenPOS website and may trigger a reindex.</comment>');

        $question = new ConfirmationQuestion('Would you like to continue? [y/n] ', false);
        if(!$helper->ask($input, $output, $question)) {
            return 0;
        }
        
        if(!$this->openPosConfiguration->getPosStore()) {
            $output->writeln('<error>It looks like you haven\'t configured OpenPOS yet or the POS store has not been set!</error>');
            $output->writeln('<fg=black;bg=cyan>Check the POS store is set or run bin/magento openpos:setup-wizard</>');
            return 1;
        }

        $websiteId = $this->openPosConfiguration->getPosStore()->getWebsiteId();

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addFieldToSelect('entity_id');
        $productIds = $productCollection->getAllIds();

        if(count($productIds) === 0) {
            $output->writeln('<error>Error: could not find any products in Magento!</error>');
            return 1;
        }

        $output->writeln(sprintf('<comment>Found %d product(s).</comment>', count($productIds)));
        $output->writeln(sprintf('<comment>Assigning, this may take a while.</comment>', count($productIds)));

        $this->productAction->updateWebsites($productIds, [$websiteId], 'add');

        $output->writeln(sprintf('<comment>%d product(s) assigned to website ID: %d.</comment>', count($productIds), $websiteId));
        $output->writeln('<info>Product assigning is complete.</info>');
        
        return 0;
    }
}
