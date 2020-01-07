<?php
namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Quote\Model;

use Magento\NegotiableQuote\Model\Plugin\Quote\Model\ShippingAssignmentPersisterPlugin;

/**
 * Class ShippingAssignmentPersistentPlugin
 */
class ShippingAssignmentPersistentPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShippingAssignmentPersisterPlugin
     */
    private $shippingAssignmentPersistentPlugin;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->shippingAssignmentPersistentPlugin =
            new ShippingAssignmentPersisterPlugin();
    }

    /**
     * Test for method aroundSave
     */
    public function testAroundSave()
    {
        $subject = $this->createMock(\Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister::class);
        $quote = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false
        );
        $shippingAssignment = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\ShippingAssignmentInterface::class,
            [],
            '',
            false
        );
        $proceed = function () use ($quote, $shippingAssignment) {
            return [
                $quote,
                $shippingAssignment
            ];
        };
        $quote->expects($this->once())->method('getIsActive')->willReturn(true);

        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getNegotiableQuote']
        );
        $quote->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $negotiableQuote = $this->getMockForAbstractClass(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class,
            [],
            '',
            false
        );
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn(true);
        $quote->expects($this->any())->method('setIsActive')->willReturn(true);

        $this->shippingAssignmentPersistentPlugin->aroundSave($subject, $proceed, $quote, $shippingAssignment);
    }
}
