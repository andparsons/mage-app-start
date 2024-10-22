<?php

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Transaction;

use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class ManagerTest
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Manager
     */
    private $manager;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * Init
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->repositoryMock = $this->createMock(\Magento\Sales\Model\Order\Payment\Transaction\Repository::class);
        $this->manager = $objectManager->getObject(
            \Magento\Sales\Model\Order\Payment\Transaction\Manager::class,
            ['transactionRepository' => $this->repositoryMock]
        );
    }

    /**
     * @dataProvider getAuthorizationDataProvider
     * @param $parentTransactionId
     * @param $paymentId
     * @param $orderId
     */
    public function testGetAuthorizationTransaction($parentTransactionId, $paymentId, $orderId)
    {
        $transaction = $this->createMock(\Magento\Sales\Model\Order\Payment\Transaction::class);
        if ($parentTransactionId) {
            $this->repositoryMock->expects($this->once())->method('getByTransactionId')->with(
                $parentTransactionId,
                $paymentId,
                $orderId
            )->willReturn($transaction);
        } else {
            $this->repositoryMock->expects($this->once())->method('getByTransactionType')->with(
                Transaction::TYPE_AUTH,
                $paymentId,
                $orderId
            )->willReturn($transaction);
        }
        $this->assertEquals(
            $transaction,
            $this->manager->getAuthorizationTransaction($parentTransactionId, $paymentId, $orderId)
        );
    }

    /**
     * @dataProvider isTransactionExistsDataProvider
     * @param string|null $transactionId
     * @param bool $isRepositoryReturnTransaction
     * @param bool $expectedResult
     */
    public function testIsTransactionExists($transactionId, $isRepositoryReturnTransaction, $expectedResult)
    {
        $paymentId = 1;
        $orderId = 9;

        if ($transactionId && $isRepositoryReturnTransaction) {
            $transaction = $this->createMock(\Magento\Sales\Model\Order\Payment\Transaction::class);
            $this->repositoryMock->expects($this->once())->method('getByTransactionId')->willReturn($transaction);
        }

        $this->assertEquals(
            $expectedResult,
            $this->manager->isTransactionExists($transactionId, $paymentId, $orderId)
        );
    }

    /**
     * @dataProvider generateTransactionIdDataProvider
     * @param string|null $transactionId
     * @param string|null $parentTransactionId
     * @param string|null $transactionBasedTxnId
     * @param string $type
     * @param string|null $expectedResult
     */
    public function testGenerateTransactionId(
        $transactionId,
        $parentTransactionId,
        $transactionBasedTxnId,
        $type,
        $expectedResult
    ) {
        $transactionBasedOn = false;

        $payment = $this->createPartialMock(
            \Magento\Sales\Model\Order\Payment::class,
            ["setParentTransactionId", "getParentTransactionId", "getTransactionId"]
        );
        $payment->expects($this->atLeastOnce())->method('getTransactionId')->willReturn($transactionId);

        if (!$parentTransactionId && !$transactionId && $transactionBasedTxnId) {
            $transactionBasedOn = $this->createMock(\Magento\Sales\Model\Order\Payment\Transaction::class);
            $transactionBasedOn->expects($this->once())->method('getTxnId')->willReturn($transactionBasedTxnId);
            $payment->expects($this->once())->method("setParentTransactionId")->with($transactionBasedTxnId);
        }
        $payment->expects($this->exactly(2))->method('getParentTransactionId')->willReturnOnConsecutiveCalls(
            $parentTransactionId,
            $transactionBasedOn ? $transactionBasedTxnId : $parentTransactionId
        );

        $this->assertEquals(
            $expectedResult,
            $this->manager->generateTransactionId($payment, $type, $transactionBasedOn)
        );
    }

    /**
     * @return array$transactionId, $parentTransactionId, $transactionBasedTxnId
     */
    public function generateTransactionIdDataProvider()
    {
        return [
            'withoutTransactionId' => [
                'transactionId' => null,
                'parentTransactionId' => 2,
                'transactionBasedOnId' => 1,
                'type' => Transaction::TYPE_REFUND,
                'expectedResult' => "2-" . Transaction::TYPE_REFUND
            ],
            'withTransactionId' => [
                'transactionId' => 33,
                'parentTransactionId' => 2,
                'transactionBasedOnId' => 1,
                'type' => Transaction::TYPE_REFUND,
                'expectedResult' => 33
            ],
            'withBasedTransactionId' => [
                'transactionId' => null,
                'parentTransactionId' => null,
                'transactionBasedOnId' => 4,
                'type' => Transaction::TYPE_REFUND,
                'expectedResult' => "4-" . Transaction::TYPE_REFUND
            ],
        ];
    }

    /**
     * @return array
     */
    public function isTransactionExistsDataProvider()
    {
        return [
            'withTransactionIdAndTransaction' => ["100-refund", true, true],
            'withoutTransactionIdAndWithTransaction' => [null, true, false],
            'withTransactionIdAndWithoutTransaction' => ["100-refund", false, false],
            'withoutTransactionIdAndWithoutTransaction' => [null, false, false],
        ];
    }

    /**
     * @return array
     */
    public function getAuthorizationDataProvider()
    {
        return [
            'withParentId' => [false, 1, 1],
            'withoutParentId' => [1, 2, 1]
        ];
    }
}
