<?php
namespace Magento\NegotiableQuote\Test\Unit\Plugin\Customer\Controller\Address;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class FormPostPluginTest
 * @package Magento\NegotiableQuote\Test\Unit\Plugin\Customer\Controller\Address
 */
class FormPostPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Plugin\Customer\Controller\Address\FormPostPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resultFactoryMock =
            $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->redirectMock = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Customer\Controller\Address\FormPostPlugin::class,
            ['resultFactory' => $this->resultFactoryMock]
        );
    }

    /**
     * Test for afterExecute method
     *
     * @param mixed $quoteId
     * @dataProvider afterExecuteDataProvider
     */
    public function testAfterExecute($quoteId)
    {
        if ($quoteId) {
            $this->resultFactoryMock->expects($this->any())
                ->method('create')
                ->with(ResultFactory::TYPE_REDIRECT)
                ->will($this->returnValue($this->redirectMock));
            $this->redirectMock->expects($this->once())
                ->method('setPath')
                ->with(
                    'negotiable_quote/quote/view',
                    ['quote_id' => $quoteId]
                )
                ->will($this->returnSelf());
        }

        $subjectMock =
            $this->createPartialMock(\Magento\Customer\Controller\Address\FormPost::class, ['getRequest'], []);
        $requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getParam')
            ->with('quoteId')
            ->willReturn($quoteId);
        $subjectMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);
        $result = $this->plugin->afterExecute($subjectMock, $this->redirectMock);

        $this->assertEquals($this->redirectMock, $result);
    }

    /**
     * Data Provider for testAfterExecute
     *
     * @return array
     */
    public function afterExecuteDataProvider()
    {
        return [
            [1],
            [null],
        ];
    }
}
