<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Migrate from BlueFoot to PageBuilder
 *
 * @api
 */
class MigrateToPageBuilder implements DataPatchInterface
{
    /**
     * @var \Magento\PageBuilderDataMigration\Setup\ConvertBlueFootToPageBuilderFactory
     */
    private $convertBlueFootToPageBuilderFactory;

    /**
     * @var \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdaterFactory
     */
    private $eavConfigUpdaterFactory;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\PageBuilderDataMigration\Setup\MoveImages
     */
    private $moveImages;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\PageBuilderDataMigration\Setup\ConvertBlueFootToPageBuilderFactory $factory
     * @param \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdaterFactory $eavConfigUpdaterFactory
     * @param \Magento\PageBuilderDataMigration\Setup\MoveImages $moveImages
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\PageBuilderDataMigration\Setup\ConvertBlueFootToPageBuilderFactory $factory,
        \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdaterFactory $eavConfigUpdaterFactory,
        \Magento\PageBuilderDataMigration\Setup\MoveImages $moveImages,
        \Magento\Framework\App\State $appState
    ) {
        $this->convertBlueFootToPageBuilderFactory = $factory;
        $this->eavConfigUpdaterFactory = $eavConfigUpdaterFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
        $this->moveImages = $moveImages;
    }

    /**
     * Apply data conversion
     *
     * @return DataPatchInterface|void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function apply()
    {
        if ($this->moduleDataSetup->tableExists('gene_bluefoot_entity')) {
            /* @var $eavConfigUpdater \Magento\PageBuilderDataMigration\Setup\DataConverter\EavConfigUpdater */
            $eavConfigUpdater = $this->eavConfigUpdaterFactory->create(
                ['connection' => $this->moduleDataSetup->getConnection()]
            );
            $eavConfigUpdater->update();
            $convertBlueFootToPageBuilder = $this->convertBlueFootToPageBuilderFactory->create(
                ['setup' => $this->moduleDataSetup]
            );
            $this->appState->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                [$convertBlueFootToPageBuilder, 'convert']
            );
            $this->moveImages->move();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
