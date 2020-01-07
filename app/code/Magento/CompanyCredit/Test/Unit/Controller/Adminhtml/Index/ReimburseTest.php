<?php

namespace Magento\CompanyCredit\Test\Unit\Controller\Adminhtml\Index;

/**
 * Unit test for Reimburse controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReimburseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonFactory;

    /**
     * @var \Magento\CompanyCredit\Action\ReimburseFacade|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reimburseFacade;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceFormatter;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\CompanyCredit\Controller\Adminhtml\Index\Reimburse
     */
    private $reimburse;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->jsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reimburseFacade = $this->getMockBuilder(\Magento\CompanyCredit\Action\ReimburseFacade::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceFormatter = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->reimburse = $objectManager->getObject(
            \Magento\CompanyCredit\Controller\Adminhtml\Index\Reimburse::class,
            [
                'jsonFactory' => $this->jsonFactory,
                'reimburseFacade' => $this->reimburseFacade,
                'priceFormatter' => $this->priceFormatter,
                'logger' => $this->logger,
                '_request' => $this->request,
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $companyId = 1;
        $currencyCode = 'USD';
        $creditBalance = -10;
        $creditLimit = 50;
        $availableLimit = 40;
        $reimburseBalance = [
            'amount' => 100,
            'purchase_order' => 'O123',
            'credit_comment' => 'Some Comment',
        ];

        $this->request->expects($this->at(0))->method('getParam')->with('id')->willReturn($companyId);
        $this->request->expects($this->at(1))->method('getParam')
            ->with('reimburse_balance')->willReturn($reimburseBalance);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($result);

        $credit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->reimburseFacade->expects($this->once())
            ->method('execute')
            ->with(
                $companyId,
                $reimburseBalance['amount'],
                $reimburseBalance['credit_comment'],
                $reimburseBalance['purchase_order']
            )->willReturn($credit);

        $credit->expects($this->exactly(1))->method('getCurrencyCode')->willReturn($currencyCode);
        $credit->expects($this->exactly(2))->method('getBalance')->willReturn($creditBalance);
        $credit->expects($this->once())->method('getCreditLimit')->willReturn($creditLimit);
        $credit->expects($this->once())->method('getAvailableLimit')->willReturn($availableLimit);

        $this->priceFormatter->expects($this->at(0))->method('format')
            ->with($creditBalance, false, null, null, $currencyCode)->willReturn('$' . $creditBalance);
        $this->priceFormatter->expects($this->at(1))->method('format')
            ->with($creditLimit, false, null, null, $currencyCode)->willReturn('$' . $creditLimit);
        $this->priceFormatter->expects($this->at(2))->method('format')
            ->with($availableLimit, false, null, null, $currencyCode)->willReturn('$' . $availableLimit);

        $result->expects($this->once())->method('setData')->with(
            [
                'status' => 'success',
                'balance' => [
                    'outstanding_balance' => '$' . $creditBalance,
                    'is_negative' => true,
                    'credit_limit' => '$' . $creditLimit,
                    'available_credit' => '$' . $availableLimit
                ]
            ]
        )->willReturnSelf();

        $this->assertEquals($result, $this->reimburse->execute());
    }
    
    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $message = __('No such entity');
        $exception = new \Magento\Framework\Exception\NoSuchEntityException($message);

        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonFactory->expects($this->once())->method('create')->willReturn($result);
        $this->reimburseFacade->expects($this->once())
            ->method('execute')
            ->willThrowException($exception);
        $result->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'error' => $message,
            ]
        )->willReturnSelf();

        $this->assertEquals($result, $this->reimburse->execute());
    }
}
