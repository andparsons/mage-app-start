<?php

namespace Magento\CompanyCredit\Test\Unit\Model\ResourceModel;

/**
 * Class CreditLimitTest.
 */
class CreditLimitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit
     */
    private $creditLimit;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->priceCurrency = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $resources = $this->createMock(
            \Magento\Framework\App\ResourceConnection::class
        );
        $this->connection = $this->createMock(
            \Magento\Framework\DB\Adapter\AdapterInterface::class
        );
        $resources->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->connection);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditLimit = $objectManager->getObject(
            \Magento\CompanyCredit\Model\ResourceModel\CreditLimit::class,
            [
                'priceCurrency' => $this->priceCurrency,
                '_resources' => $resources,
            ]
        );
    }

    /**
     * Test for method changeBalance.
     *
     * @return void
     */
    public function testChangeBalance()
    {
        $creditId = 1;
        $value = 15;
        $convertedValue = 12;
        $currency = 'USD';
        $condition = 'entity_id=' . $creditId;
        $rowData = [
            \Magento\CompanyCredit\Api\Data\CreditLimitInterface::BALANCE => 25,
            \Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE => 'EUR',
        ];

        $this->connection->expects($this->once())
            ->method('quoteInto')->with('entity_id=?', $creditId)->willReturn($condition);
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select->expects($this->once())->method('where')->with($condition)->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchRow')->willReturn($rowData);
        $operationCurrency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->priceCurrency->expects($this->once())
            ->method('getCurrency')->with(true, $currency)->willReturn($operationCurrency);
        $operationCurrency->expects($this->once())->method('convert')->with(
            $value,
            $rowData[\Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE]
        )->willReturn($convertedValue);
        $operationCurrency->expects($this->once())->method('getRate')
            ->with($rowData[\Magento\CompanyCredit\Api\Data\CreditLimitInterface::CURRENCY_CODE])
            ->willReturn(0.7);
        $this->connection->expects($this->once())->method('update')->with(
            null,
            [\Magento\CompanyCredit\Api\Data\CreditLimitInterface::BALANCE => 37],
            $condition
        );
        $this->creditLimit->changeBalance($creditId, $value, $currency);
    }
}
