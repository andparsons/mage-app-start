<?php

namespace Magento\Company\Test\Unit\Controller\Customer;

/**
 * Class GetTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Customer\Get
     */
    private $get;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManager;

    /**
     * @var \Magento\Company\Api\AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $acl;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->structureManager = $this->createMock(\Magento\Company\Model\Company\Structure::class);
        $this->structureManager->expects($this->once())->method('getAllowedIds')->willReturn(
            ['users' => [1, 2, 5, 7]]
        );
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->acl = $this->createMock(\Magento\Company\Api\AclInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->resultJson = $this->createPartialMock(\Magento\Framework\Controller\Result\Json::class, ['setData']);
        $resultFactory->expects($this->once())->method('create')->willReturn($this->resultJson);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->get = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Customer\Get::class,
            [
                'resultFactory' => $resultFactory,
                'structureManager' => $this->structureManager,
                'customerRepository' => $this->customerRepository,
                'acl' => $this->acl,
                'logger' => $logger,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @param int $customerId
     * @param \PHPUnit_Framework_MockObject_MockObject $customer
     * @param \PHPUnit\Framework\MockObject\Stub\ReturnStub|\PHPUnit\Framework\MockObject\Stub\Exception $customerResult
     * @param int $getCustomerInvocation
     * @param int $invocationCount
     * @param string $expect
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $customerId,
        $customer,
        $customerResult,
        $getCustomerInvocation,
        $invocationCount,
        $expect
    ) {
        $this->request->expects($this->once())->method('getParam')->with('customer_id')->willReturn($customerId);
        $companyAttributes = $this->createMock(\Magento\Company\Model\Customer::class);
        $this->customerRepository->expects($this->exactly($getCustomerInvocation))
            ->method('getById')->with($customerId)->will($customerResult);
        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $customerExtension->expects($invocationCount ? $this->atLeastOnce() : $this->never())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $customer->expects($invocationCount ? $this->atLeastOnce() : $this->never())
            ->method('getExtensionAttributes')->willReturn($customerExtension);
        $customer->expects($this->exactly($invocationCount))->method('__toArray')->willReturn([]);
        $companyAttributes->expects($this->exactly($invocationCount))->method('getJobTitle')->willReturn('job title');
        $companyAttributes->expects($this->exactly($invocationCount))->method('getTelephone')->willReturn('111111');
        $companyAttributes->expects($this->exactly($invocationCount))->method('getStatus')->willReturn('status');
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $this->acl->expects($this->exactly($invocationCount))
            ->method('getRolesByUserId')->with($customerId)->willReturn([$role]);
        $role->expects($this->exactly($invocationCount))->method('getId')->willReturn(9);
        $result = '';
        $setDataCallback = function ($data) use (&$result) {
            $result = $data['status'];
        };
        $this->resultJson->expects($this->once())->method('setData')->willReturnCallback($setDataCallback);
        $this->get->execute();
        $this->assertEquals($expect, $result);
    }

    /**
     * Data provider for testExecute.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        $customer = $this->createMock(\Magento\Customer\Model\Data\Customer::class);
        return [
            [
                1,
                $customer,
                $this->returnValue($customer),
                1,
                1,
                'ok'
            ],
            [
                2,
                $customer,
                $this->throwException(new \Exception()),
                1,
                0,
                'error'
            ],
            [
                2,
                $customer,
                $this->throwException(new \Magento\Framework\Exception\LocalizedException(__('phrase'))),
                1,
                0,
                'error'
            ],
            [
                4,
                $customer,
                $this->returnValue(null),
                0,
                0,
                'error'
            ],
        ];
    }
}
