<?php
namespace Magento\SalesRule\Test\Unit\Cron;

class AggregateSalesReportCouponsDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Cron\AggregateSalesReportCouponsData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Locale\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Report\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportRule;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            \Magento\SalesRule\Cron\AggregateSalesReportCouponsData::class,
            [
                'reportRule' => $this->reportRule,
                'localeResolver' => $this->localeResolver,
                'localeDate' => $this->localeDate,
            ]
        );
    }

    protected function initMocks()
    {
        $this->localeResolver = $this->createMock(\Magento\Framework\Locale\Resolver::class);
        $this->localeDate = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime\Timezone::class, ['date']);
        $this->reportRule = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Report\Rule::class);
    }

    public function testExecute()
    {
        $data = new \DateTime();
        $this->localeResolver->expects($this->once())
            ->method('emulate')
            ->with(0);
        $this->localeDate->expects($this->once())
            ->method('date')
            ->will($this->returnValue($data));
        $this->reportRule->expects($this->once())
            ->method('aggregate')
            ->with($data);
        $this->localeResolver->expects($this->once())
            ->method('revert');

        $scheduleMock = $this->createMock(\Magento\Cron\Model\Schedule::class);

        $this->assertEquals($this->model, $this->model->execute($scheduleMock));
    }
}
