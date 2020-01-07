<?php

namespace Magento\Company\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\PageFactory;

/**
 * Class AddAccessViolationPageAndAssignB2CCustomers
 * @package Magento\Company\Setup\Patch\Data
 */
class AddAccessViolationPageAndAssignB2CCustomers implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Framework\DB\Query\Generator
     */
    private $queryGenerator;

    /**
     * @var int
     */
    private $batchSizeForCustomers = 10000;

    /**
     * AddAccessViolationPageAndAssignB2CCustomers constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param PageFactory $pageFactory
     * @param \Magento\Framework\DB\Query\Generator $queryGenerator
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        PageFactory $pageFactory,
        \Magento\Framework\DB\Query\Generator $queryGenerator
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
        $this->queryGenerator = $queryGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $pageData = [
            'title' => 'Company: Access Denied',
            'page_layout' => '2columns-right',
            'meta_keywords' => 'Page keywords',
            'meta_description' => 'Page description',
            'identifier' => 'access-denied-page',
            'content_heading' => 'Access Denied',
            'content' => 'You do not have permissions to view this page. '
                .'If you believe this is a mistake, please contact your company administrator.',
            'layout_update_xml' => '<referenceContainer name="root">'
                . '<referenceBlock name="breadcrumbs" remove="true"/>'
                . '</referenceContainer>',
            'is_active' => 1,
            'stores' => [0],
            'sort_order' => 0
        ];

        $this->moduleDataSetup->startSetup();
        $this->pageFactory->create()->setData($pageData)->save();
        $this->fillCustomers();
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Fill Company extension with B2C customers if it is installing over B2C Magento edition.
     *
     * @return void
     */
    private function fillCustomers()
    {
        $companyCustomerTableName = $this->moduleDataSetup->getTable('company_advanced_customer_entity');
        $customerTableName = $this->moduleDataSetup->getTable('customer_entity');

        $select = $this->moduleDataSetup->getConnection()->select()
            ->from(
                ['customer' => $customerTableName],
                ['entity_id']
            )->joinLeft(
                ['company_customer' => $companyCustomerTableName],
                'customer.entity_id = company_customer.customer_id',
                []
            )->where(
                'company_customer.customer_id is NULL'
            );

        $iterator = $this->queryGenerator->generate('entity_id', $select, $this->batchSizeForCustomers);
        foreach ($iterator as $selectByRange) {
            $this->moduleDataSetup->getConnection()->query(
                $this->moduleDataSetup->getConnection()->insertFromSelect(
                    $selectByRange,
                    $companyCustomerTableName,
                    ['customer_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
