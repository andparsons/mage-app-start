<?php
namespace Magento\Company\Test\Unit\Block\Link;

use Magento\Company\Block\Link\Current;
use Magento\Company\Model\CompanyContext;

/**
 * Class CurrentTest
 */
class CurrentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Current
     */
    private $model;

    public function testConstruct()
    {
        $resourceValue = 'resource_value';
        $data = [
            'resource' => $resourceValue,
        ];

        $companyContextMock = $this->getMockBuilder(CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Current::class,
            [
                'companyContext' => $companyContextMock,
                'data' => $data,
            ]
        );

        $companyContextMock->expects($this->once())
            ->method('isResourceAllowed')
            ->with($resourceValue);

        $this->model->toHtml();
    }

    public function testConstructWithoutResource()
    {
        $companyContextMock = $this->getMockBuilder(CompanyContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Current::class,
            [
                'companyContext' => $companyContextMock,
            ]
        );

        $companyContextMock->expects($this->once())
            ->method('isResourceAllowed')
            ->with(null);

        $this->model->toHtml();
    }
}
