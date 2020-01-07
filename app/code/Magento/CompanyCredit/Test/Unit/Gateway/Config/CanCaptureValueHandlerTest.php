<?php
namespace Magento\CompanyCredit\Test\Unit\Gateway\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for CanCaptureValueHandler.
 */
class CanCaptureValueHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configInterface;

    /**
     * @var \Magento\CompanyCredit\Gateway\Config\CanCaptureValueHandler
     */
    private $canCaptureValueHandler;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->configInterface = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManager = new ObjectManagerHelper($this);
        $this->subjectReader = $this->objectManager->getObject(
            \Magento\Payment\Gateway\Helper\SubjectReader::class
        );
        $this->canCaptureValueHandler = $this->objectManager->getObject(
            \Magento\CompanyCredit\Gateway\Config\CanCaptureValueHandler::class,
            [
                'configInterface' => $this->configInterface
            ]
        );
    }

    /**
     * Test for handle method.
     *
     * @param string $status
     * @param bool $result
     * @return void
     * @dataProvider handleDataProvider
     */
    public function testHandle($status, $result)
    {
        $subject = [];
        $this->configInterface->expects($this->once())->method('getValue')->with('order_status')->willReturn($status);

        $this->assertEquals($result, $this->canCaptureValueHandler->handle($subject));
    }

    /**
     * Data provider for testHandle method.
     *
     * @return array
     */
    public function handleDataProvider()
    {
        return [
            [\Magento\Sales\Model\Order::STATE_PROCESSING, true],
            [\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, false],
        ];
    }
}
