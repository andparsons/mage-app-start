<?php

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateCustomerAttributeInputFilters
 * @package Magento\Customer\Setup\Patch
 */
class UpdateCustomerAttributeInputFilters implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * UpdateCustomerAttributeInputFilters constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityAttributes = [
            'customer_address' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
            'customer' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
        ];
        $customerSetup->upgradeAttributes($entityAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateVATNumber::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.13';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
