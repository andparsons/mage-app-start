<?php
namespace Magento\TestSetupDeclarationModule7\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Magento\TestSetupDeclarationModule7\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $adapter = $setup->getConnection();
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0') < 0) {
            $adapter->insertArray('reference_table', ['bigint_without_padding'], [6, 12, 7]);
        }

        $setup->endSetup();
    }
}
