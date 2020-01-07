<?php

namespace Magento\Company\Test\Unit\Controller\Customer;

use Magento\Company\Api\Data\CompanyCustomerInterface;

/**
 * Class DeleteTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Customer\Delete
     */
    private $delete;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->structureManager = $this->createMock(\Magento\Company\Model\Company\Structure::class);
        $this->structureManager->expects($this->once())
            ->method('getAllowedIds')->will(
                $this->returnValue(
                    [
                        'users' => [1, 2, 5, 7]
                    ]
                )
            );
        $companyContext = $this->getMockForAbstractClass(
            \Magento\Company\Model\CompanyContext::class,
            [],
            '',
            false,
            true,
            true,
            ['getCustomerId']
        );
        $companyContext->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(1);
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->resultJson = $this->createPartialMock(\Magento\Framework\Controller\Result\Json::class, ['setData']);
        $resultFactory->expects($this->once())->method('create')->will($this->returnValue($this->resultJson));
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->delete = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Customer\Delete::class,
            [
                'resultFactory' => $resultFactory,
                'structureManager' => $this->structureManager,
                'customerRepository' => $this->customerRepository,
                'logger' => $logger,
                '_request' => $this->request,
                'companyContext' => $companyContext
            ]
        );
    }

    /**
     * Test execute.
     *
     * @param int $customerId
     * @param \PHPUnit\Framework\MockObject\Stub\ReturnStub|\PHPUnit\Framework\MockObject\Stub\Exception $saveResult
     * @param \PHPUnit_Framework_MockObject_MockObject|null $structure
     * @param string $expect
     * @param int $structureCallCount
     * @param int $statusCallCount
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $customerId,
        $saveResult,
        $structure,
        $expect,
        $structureCallCount,
        $statusCallCount
    ) {
        $this->request->expects($this->once())->method('getParam')->with('customer_id')->willReturn($customerId);

        $this->structureManager->expects($this->exactly($structureCallCount))
            ->method('getStructureByCustomerId')->with($customerId)->willReturn($structure);
        $companyAttributes = $this->createMock(\Magento\Company\Api\Data\CompanyCustomerInterface::class);
        $companyAttributes->expects($this->exactly($statusCallCount))
            ->method('setStatus')->with(CompanyCustomerInterface::STATUS_INACTIVE)->willReturnSelf();
        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $customerExtension->expects($this->exactly($statusCallCount))
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->exactly($statusCallCount))->method('setStatus')->willReturnSelf();
        $this->customer->expects($this->exactly($statusCallCount))
            ->method('getExtensionAttributes')->willReturn($customerExtension);
        $this->customerRepository->expects($this->exactly($statusCallCount))
            ->method('getById')->willReturn($this->customer);
        $this->customerRepository->expects($this->exactly($statusCallCount))
            ->method('save')->with($this->customer)->will($saveResult);
        $result = '';
        $setDataCallback = function ($data) use (&$result) {
            $result = $data['status'];
        };
        $this->resultJson->expects($this->once())->method('setData')->will($this->returnCallback($setDataCallback));
        $this->delete->execute();
        $this->assertEquals($expect, $result);
    }

    /**
     * Execute data provider.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        $structure = $this->createPartialMock(\Magento\Company\Model\Company\Structure::class, ['getId']);
        return [
            [
                1,
                $this->returnValue($this->customer),
                $structure,
                'error',
                0,
                0
            ], //delete yourself
            [
                2,
                $this->returnValue($this->customer),
                $structure,
                'ok',
                1,
                1
            ],
            [
                2,
                $this->returnValue($this->customer),
                null,
                'error',
                1,
                0
            ],
            [
                2,
                $this->throwException(new \Magento\Framework\Exception\LocalizedException(__('Exception message'))),
                $structure,
                'error',
                1,
                1
            ],
            [
                2,
                $this->throwException(new \Exception()),
                $structure,
                'error',
                1,
                1
            ],
            [
                4,
                $this->throwException(new \Exception()),
                $structure,
                'error',
                0,
                0
            ],
        ];
    }
}
