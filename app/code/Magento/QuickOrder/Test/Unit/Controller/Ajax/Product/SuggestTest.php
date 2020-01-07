<?php
namespace Magento\QuickOrder\Test\Unit\Controller\Ajax\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class SuggestTest
 */
class SuggestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\QuickOrder\Controller\Ajax\Product\Suggest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $suggest;

    /**
     * @var \Magento\QuickOrder\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfigMock;

    /**
     * @var \Magento\QuickOrder\Model\Product\Suggest\DataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $suggestDataProviderMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json | \PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonResult;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect | \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectResult;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->moduleConfigMock = $this->getMockBuilder(\Magento\QuickOrder\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->suggestDataProviderMock = $this->getMockBuilder(
            \Magento\QuickOrder\Model\Product\Suggest\DataProvider::class
        )->disableOriginalConstructor()->getMock();

        $resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonResult = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectResult = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory->expects($this->any())
            ->method('create')
            ->willReturnMap([
                ['json', [], $this->jsonResult],
                ['redirect', [], $this->redirectResult]
            ]);

        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->suggest = $this->objectManagerHelper->getObject(
            \Magento\QuickOrder\Controller\Ajax\Product\Suggest::class,
            [
                'moduleConfig' => $this->moduleConfigMock,
                'suggestDataProvider' => $this->suggestDataProviderMock,
                'resultFactory' => $resultFactory,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecute()
    {
        $this->moduleConfigMock->expects($this->any())
            ->method('isActive')
            ->willReturn(true);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['q', null, 'query']
            ]);

        $this->suggestDataProviderMock->expects($this->any())
            ->method('getItems')
            ->willReturn([]);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->suggest->execute());
    }

    /**
     * Test execute disabled module
     *
     * @return void
     */
    public function testExecuteDisabledModule()
    {
        $this->moduleConfigMock->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $this->redirectResult->expects($this->once())
            ->method('setRefererUrl')
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->suggest->execute());
    }
}
