<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\CompanyQuoteConfigManagement;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;

/**
 * Test for \Magento\NegotiableQuote\Model\CompanyQuoteConfigManagement.
 */
class CompanyQuoteConfigManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompanyQuoteConfigManagement
     */
    private $companyQuoteConfigManagement;

    /**
     * @var CompanyQuoteConfigInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyQuoteConfigFactory;

    /**
     * @var CompanyQuoteConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyQuoteConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyQuoteConfigFactory =
            $this->createPartialMock(CompanyQuoteConfigInterfaceFactory::class, ['create']);
        $this->companyQuoteConfig = $this->getMockForAbstractClass(
            CompanyQuoteConfigInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['load']
        );
        $this->companyQuoteConfigFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->companyQuoteConfig);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyQuoteConfigManagement = $objectManager->getObject(
            CompanyQuoteConfigManagement::class,
            [
                'companyQuoteConfigFactory' => $this->companyQuoteConfigFactory,
            ]
        );
    }

    /**
     * Test for method getByCompanyId.
     *
     * @return void
     */
    public function testGetByCompanyId()
    {
        $companyId = 42;
        $this->companyQuoteConfig->expects($this->once())->method('load')->with($companyId)->willReturnSelf();

        $this->assertEquals($this->companyQuoteConfig, $this->companyQuoteConfigManagement->getByCompanyId($companyId));
    }
}
