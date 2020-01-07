<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provide CLI command to migrate Page Builder content
 */
class MigrateCommand extends Command
{
    /**
     * @var \Magento\Setup\Module\DataSetup
     */
    private $setup;

    /**
     * @var \Magento\PageBuilderDataMigration\Setup\ConvertBlueFootToPageBuilderFactory
     */
    private $factory;

    /**
     * @var \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdaterFactory
     */
    private $eavConfigUpdaterFactory;

    /**
     * @var \Magento\PageBuilderDataMigration\Setup\MoveImages
     */
    private $moveImages;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    private $cacheManager;

    /**
     * @param \Magento\Setup\Module\DataSetup $setup
     * @param \Magento\PageBuilderDataMigration\Setup\ConvertBlueFootToPageBuilderFactory $factory
     * @param \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdaterFactory $eavConfigUpdaterFactory
     * @param \Magento\PageBuilderDataMigration\Setup\MoveImages $moveImages
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     */
    public function __construct(
        \Magento\Setup\Module\DataSetup $setup,
        \Magento\PageBuilderDataMigration\Setup\ConvertBlueFootToPageBuilderFactory $factory,
        \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdaterFactory $eavConfigUpdaterFactory,
        \Magento\PageBuilderDataMigration\Setup\MoveImages $moveImages,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Cache\Manager $cacheManager
    ) {
        parent::__construct();
        $this->setup = $setup;
        $this->factory = $factory;
        $this->eavConfigUpdaterFactory = $eavConfigUpdaterFactory;
        $this->moveImages = $moveImages;
        $this->appState = $appState;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('pagebuilder:migrate')
            ->setDescription('Migrate BlueFootCMS data into Magento Page Builder.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Verify BlueFoot tables exist
            if (!$this->setup->tableExists('gene_bluefoot_entity')) {
                throw new \Exception("BlueFoot tables are not present in current database.");
            }

            /* @var $eavConfigUpdater \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdater */
            $output->writeln('Updating EAV configuration.');
            $eavConfigUpdater = $this->eavConfigUpdaterFactory->create(
                ['connection' => $this->setup->getConnection()]
            );
            $eavConfigUpdater->update();

            $output->writeln('Migrating content types.');
            $convertBlueFootToPageBuilder = $this->factory->create(
                ['setup' => $this->setup]
            );
            $this->appState->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                [$convertBlueFootToPageBuilder, 'convert']
            );

            $output->writeln('Moving images to new location.');
            $this->moveImages->move();

            $output->writeln('Clearing block_html cache.');
            $this->cacheManager->flush([\Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER]);

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            return Cli::RETURN_FAILURE;
        }
    }
}
