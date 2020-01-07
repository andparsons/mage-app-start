<?php

namespace Magento\CompanyCredit\Test\Unit\Plugin\Company\Model\ResourceModel\Company\Grid;

/**
 * Class CollectionPluginTest.
 */
class CollectionPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Plugin\Company\Model\ResourceModel\Company\Grid\CollectionPlugin
     */
    private $collectionPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collectionPlugin = $objectManager->getObject(
            \Magento\CompanyCredit\Plugin\Company\Model\ResourceModel\Company\Grid\CollectionPlugin::class
        );
    }

    /**
     * Test beforeLoadWithFilter method.
     *
     * @return void
     */
    public function testAfterGetCompanyResultData()
    {
        $companyCollection = $this->createPartialMock(
            \Magento\Company\Model\ResourceModel\Company\Grid\Collection::class,
            ['getSelect', 'getTable']
        );
        $select = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            ['joinLeft']
        );
        $companyCollection->expects($this->once())
            ->method('getSelect')
            ->willReturn($select);
        $companyCollection->expects($this->once())
            ->method('getTable')
            ->with('company_credit')
            ->willReturn('company_credit');
        $select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['company_credit' => 'company_credit'],
                'company_credit.company_id = main_table.entity_id',
                ['company_credit.credit_limit', 'company_credit.balance', 'company_credit.currency_code']
            )
            ->willReturnSelf();
        $this->collectionPlugin->beforeLoadWithFilter($companyCollection);
    }
}
