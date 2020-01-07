<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class ExpirationTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpirationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Expiration
     */
    private $expiration;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDate;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolver;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);

        $this->localeDate = $this
            ->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->localeDate->expects($this->any())->method('formatDateTime')->will($this->returnArgument(1));

        $this->scopeConfig = $this
            ->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->resolver = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->expiration = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Expiration::class,
            [
                'localeDate' => $this->localeDate,
                'scopeConfig' => $this->scopeConfig,
                'resolver' => $this->resolver
            ]
        );
    }

    /**
     * Test getExpirationPeriodTime method.
     *
     * @param string|null $time
     * @param string $status
     * @param string $timezone
     * @param \DateTime $expect
     * @dataProvider expirationPeriodDataDataProvider
     * @return void
     */
    public function testGetExpirationPeriodTime($time, string $status, string $timezone, \DateTime $expect): void
    {
        $extensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $this->quote
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));

        $quoteNegotiation = $this->createMock(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class);
        $quoteNegotiation->expects($this->any())
            ->method('getExpirationPeriod')->will($this->returnValue($time));
        $quoteNegotiation->expects($this->any())
            ->method('getStatus')->will($this->returnValue($status));
        $this->quote
            ->getExtensionAttributes()
            ->expects($this->any())
            ->method('getNegotiableQuote')
            ->will($this->returnValue($quoteNegotiation));
        if ($time !== null) {
            $this->localeDate->expects($this->once())
                ->method('getConfigTimezone')
                ->willReturn($timezone);
        }
        if (empty($time)) {
            $this->scopeConfig->expects($this->at(0))
                ->method('getValue')->will($this->returnValue(5));
            $this->scopeConfig->expects($this->at(1))
                ->method('getValue')->will($this->returnValue('day'));
            $this->localeDate->expects($this->any())
                ->method('date')->will($this->returnValue($expect));
        }

        $this->assertEquals($expect, $this->expiration->getExpirationPeriodTime($this->quote));
    }

    /**
     * Data provider for getExpirationPeriodTime method.
     *
     * @return array
     */
    public function expirationPeriodDataDataProvider()
    {
        $time = time();
        $date = new \DateTime();
        $date->setTimestamp($time);
        $timezone = $date->getTimezone()->getName();
        return [
            [$date->format('c'), NegotiableQuoteInterface::STATUS_CREATED, $timezone, $date],
            [null, NegotiableQuoteInterface::STATUS_CREATED, $timezone, $date],
        ];
    }

    /**
     * Test for method getExpirationPeriodTime.
     *
     * @return void
     */
    public function testGetExpirationPeriodTimeEmpty()
    {
        $this->quote
            ->expects($this->any())
            ->method('getExtensionAttributes')
            ->will($this->returnValue(null));

        $this->assertEquals(null, $this->expiration->getExpirationPeriodTime($this->quote));
    }
}
