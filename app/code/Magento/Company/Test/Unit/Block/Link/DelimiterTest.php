<?php
namespace Magento\Company\Test\Unit\Block\Link;

use Magento\Company\Block\Link\Delimiter;
use Magento\Company\Model\CompanyContext;

/**
 * Class DelimiterTest
 */
class DelimiterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Delimiter
     */
    private $model;

    public function testConstruct()
    {
        $resourceValueOne = 'resource_value_1';
        $resourceValueTwo = 'resource_value_2';
        $data = [
            'resources' => [
                $resourceValueOne,
                $resourceValueTwo,
            ],
        ];

        $companyContextMock = $this->getMockBuilder(CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Delimiter::class,
            [
                'companyContext' => $companyContextMock,
                'data' => $data,
            ]
        );

        $companyContextMock->expects($this->exactly(2))
            ->method('isResourceAllowed')
            ->willReturnMap([
                [$resourceValueOne, false],
                [$resourceValueTwo, false],
            ]);
        $companyContextMock->expects($this->once())
            ->method('isModuleActive')
            ->willReturn(true);

        $this->model->toHtml();
    }

    public function testConstructWithoutResource()
    {
        $companyContextMock = $this->getMockBuilder(CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Delimiter::class,
            [
                'companyContext' => $companyContextMock,
            ]
        );

        $companyContextMock->expects($this->never())
            ->method('isResourceAllowed');
        $companyContextMock->expects($this->once())
            ->method('isModuleActive')
            ->willReturn(true);

        $this->model->toHtml();
    }
}
