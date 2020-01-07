<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Class RemoveNegotiationTest
 */
class RemoveNegotiationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\RemoveNegotiation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $removeNegotiation;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $json;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->negotiableQuoteManagement = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        );
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->json = $this->createPartialMock(\Magento\Framework\Controller\Result\Json::class, ['setData']);
        $this->settingsProvider = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\SettingsProvider::class,
            ['retrieveJsonError', 'retrieveJsonSuccess']
        );
        $this->settingsProvider->expects($this->once())->method('retrieveJsonSuccess')->willReturn($this->json);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $resource = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $resource->expects($this->at(0))
            ->method('getParam')->with('quote_id')->will($this->returnValue(1));
        $this->removeNegotiation = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\RemoveNegotiation::class,
            [
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'logger' => $this->logger,
                '_request' => $resource,
                'settingsProvider' => $this->settingsProvider
            ]
        );
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $this->negotiableQuoteManagement->expects($this->once())->method('removeNegotiation');
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->removeNegotiation->execute());
    }

    /**
     * Test execute
     */
    public function testExecuteWithException()
    {
        $this->negotiableQuoteManagement->expects($this->any())
            ->method('removeNegotiation')
            ->willThrowException(new \Exception);
        $this->settingsProvider->expects($this->once())->method('retrieveJsonError')->willReturn($this->json);
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->removeNegotiation->execute());
    }
}
