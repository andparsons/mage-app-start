<?php
declare(strict_types=1);

namespace Magento\ServicesId\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\ServicesId\Exception\InstanceIdGenerationException;
use Magento\ServicesId\Model\GeneratorInterface;
use Magento\ServicesId\Model\ServicesConfig;
use Psr\Log\LoggerInterface;

/**
 * Initialize config data
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $configReader;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $configReader
     * @param WriterInterface $configWriter
     * @param GeneratorInterface $generator
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $configReader,
        WriterInterface $configWriter,
        GeneratorInterface $generator,
        LoggerInterface $logger
    ) {
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->generator = $generator;
        $this->logger = $logger;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        try {
            if ($this->configReader->getValue(ServicesConfig::CONFIG_PATH_INSTANCE_ID) == null) {
                $uuid = $this->generator->generateInstanceId();
                $uuidString = strtolower($uuid);
                $this->configWriter->save(ServicesConfig::CONFIG_PATH_INSTANCE_ID, $uuidString);
            }
        } catch (InstanceIdGenerationException $e) {
            $this->logger->error(__('Failed to generate Instance ID'));
        }

        $setup->endSetup();
    }
}
