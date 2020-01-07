<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Sales\Model\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\NegotiableQuote\Model\NegotiableQuote;
use Magento\NegotiableQuote\Plugin\Sales\Model\Order\ForceNegotiableQuoteRulesPlugin;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;

/**
 * Test for Magento\NegotiableQuote\Plugin\Sales\Model\Order\ForceNegotiableQuoteRulesPlugin.
 */
class ForceNegotiableQuoteRulesPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var ForceNegotiableQuoteRulesPlugin
     */
    private $forceNegotiableQuoteRulesPluginPlugin;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->quote = $this->createMock(CartInterface::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $objectManager = new ObjectManager($this);
        $this->forceNegotiableQuoteRulesPluginPlugin = $objectManager->getObject(
            ForceNegotiableQuoteRulesPlugin::class,
            [
                'quoteRepository' => $this->quoteRepository
            ]
        );
    }

    /**
     * Test aroundPlace method.
     */
    public function testAroundPlaceWithoutException()
    {
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($this->quote);
        $this->aroundPlace(true);
    }

    /**
     * Test aroundPlace method with exception.
     */
    public function testAroundPlaceWithNoSuchEntityException()
    {
        $phrase = new Phrase(__('Exception'));
        $exception = new NoSuchEntityException($phrase);
        $this->quoteRepository->expects($this->once())->method('get')->willThrowException($exception);
        $this->aroundPlace(false);
    }

    /**
     * Test base.
     *
     * @param bool $withoutException
     */
    private function aroundPlace(bool $withoutException)
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getQuoteId',
                    'getDiscountAmount',
                    'getAppliedRuleIds',
                    'setDiscountAmount',
                    'setAppliedRuleIds'
                ]
            )
            ->getMock();
        $order->expects($this->exactly(2))
            ->method('getQuoteId')
            ->willReturn(1);
        $order->expects($this->exactly($withoutException ? 2 : 1))
            ->method('getDiscountAmount')
            ->willReturn(0);
        $order->expects($this->exactly($withoutException ? 1 : 0))
            ->method('getAppliedRuleIds')
            ->willReturn([1]);
        $order->expects($this->exactly($withoutException ? 2 : 0))
            ->method('setDiscountAmount')
            ->withConsecutive([1], [0]);
        $order->expects($this->exactly($withoutException ? 2 : 0))
            ->method('setAppliedRuleIds')
            ->withConsecutive([[1]], []);

        $negotiableQuote = $this->createMock(NegotiableQuote::class);
        $negotiableQuote->expects($this->exactly($withoutException ? 2 : 0))
            ->method('getAppliedRuleIds')
            ->willReturn([1]);

        $extensionAttributes = $this->getMockForAbstractClass(
            CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $extensionAttributes->expects($this->exactly($withoutException ? 2 : 0))
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);

        $this->quote->expects($this->exactly($withoutException ? 3 : 0))
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $proceed = static function () use ($order) {
            return $order;
        };

        $this->assertInstanceOf(
            Order::class,
            $this->forceNegotiableQuoteRulesPluginPlugin->aroundPlace($order, $proceed)
        );
    }
}
