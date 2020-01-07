<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Customer;

/**
 * Unit test for Magento\Company\Controller\Adminhtml\Customer\SuperUserValidator class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuperUserValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Customer\CompanyAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAttributes;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Controller\Adminhtml\Customer\SuperUserValidator
     */
    private $superUserValidator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->companyAttributes = $this->getMockBuilder(\Magento\Company\Model\Customer\CompanyAttributes::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->superUserValidator = $objectManager->getObject(
            \Magento\Company\Controller\Adminhtml\Customer\SuperUserValidator::class,
            [
                'companyAttributes' => $this->companyAttributes,
                'customerRepository' => $this->customerRepository,
                'resultFactory' => $this->resultFactory,
                'request' => $this->request,
                'companyRepository' => $this->companyRepository,
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @param array $customerIds
     * @param int $superUserId
     * @param bool $deletable
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        array $customerIds,
        $superUserId,
        $deletable
    ) {
        $this->request->expects($this->once())->method('getParam')->with('customer_ids')->willReturn($customerIds);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($resultJson);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyCustomer = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->willReturn($customer);
        $this->companyAttributes->expects($this->once())
            ->method('getCompanyAttributesByCustomer')
            ->with($customer)
            ->willReturn($companyCustomer);
        $companyCustomer->expects($this->once())->method('getCompanyId')->willReturn(1);
        $this->companyRepository->expects($this->once())->method('get')->with(1)->willReturn($company);
        $company->expects($this->once())->method('getSuperUserId')->willReturn($superUserId);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with(['deletable' => $deletable])
            ->willReturnSelf();

        $this->assertEquals($resultJson, $this->superUserValidator->execute());
    }

    /**
     * Data provider foe execute method.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [[1], 1, false],
            [[1], 2, true]
        ];
    }

    /**
     * Test execute method if customer doesn't exist.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\NoSuchEntityException($phrase);
        $customerIds = [99999, 100000];
        $this->request->expects($this->once())->method('getParam')->with('customer_ids')->willReturn($customerIds);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($resultJson);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->willThrowException($exception);
        $resultJson->expects($this->once())
            ->method('setData')
            ->with(['deletable' => false])
            ->willReturnSelf();

        $this->assertEquals($resultJson, $this->superUserValidator->execute());
    }
}
