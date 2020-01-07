<?php

namespace Magento\Company\Test\Unit\Controller\Customer;

/**
 * Class CheckTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Controller\Customer\Check
     */
    private $check;

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
        $this->structureManager->expects($this->any())
            ->method('getAllowedIds')->will(
                $this->returnValue(['users' => [1, 2, 5, 7]])
            );
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->resultJson = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Json::class,
            ['setData']
        );
        $resultFactory->expects($this->any())
            ->method('create')->willReturn($this->resultJson);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $companyAttributes = $this->createMock(\Magento\Company\Api\Data\CompanyCustomerInterface::class);

        $companyAttributes->expects($this->any())->method('getCompanyId')
            ->will($this->returnValue(1));
        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $customerExtension->expects($this->any())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
        $customer->expects($this->any())->method('setCustomAttribute')
            ->will($this->returnSelf());
        $this->customerRepository->expects($this->any())->method('getById')
            ->will($this->returnValue($customer));

        $this->request->expects($this->once())->method('getParam')->with('email')->willReturn('test@test.com');

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->check = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Customer\Check::class,
            [
                'resultFactory' => $resultFactory,
                'structureManager' => $this->structureManager,
                'customerRepository' => $this->customerRepository,
                'logger' => $logger,
                '_request' => $this->request
            ]
        );
    }

    /**
     * function testExecuteEmptyUser.
     *
     * @return void
     */
    public function testExecuteEmptyUser()
    {
        $this->customerRepository->expects($this->any())->method('get')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $this->resultJson->expects($this->once())->method('setData')->willReturn(
            [
                'status' => 'ok',
                'message' => '',
                'data' => []
            ]
        );
        $this->check->execute();
    }

    /**
     * function testExecuteLocalizedException.
     *
     * @return void
     */
    public function testExecuteLocalizedException()
    {
        $phrase = new \Magento\Framework\Phrase('test');
        $this->customerRepository->expects($this->any())->method('get')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException($phrase));

        $this->resultJson->expects($this->once())->method('setData')->willReturn(
            [
            'status' => 'error',
                'message' => 'test',
                'data' => []
            ]
        );
        $this->check->execute();
    }

    /**
     * function testExecuteException.
     *
     * @return void
     */
    public function testExecuteException()
    {
        $this->customerRepository->expects($this->any())->method('get')
            ->willThrowException(new \Exception());

        $this->resultJson->expects($this->once())->method('setData')->willReturn(
            [
            'status' => 'error',
                'message' => 'Something went wrong.',
                'data' => []
            ]
        );
        $this->check->execute();
    }

    /**
     * function testExecuteWithErrorCustomerExist.
     *
     * @return void
     */
    public function testExecuteWithErrorCustomerExist()
    {
        $companyAttributes = $this->createMock(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        $companyAttributes->expects($this->any())->method('getCompanyId')
            ->will($this->returnValue(2));

        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $customerExtension->expects($this->any())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        $customer = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $customer->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
        $customer->expects($this->any())->method('setCustomAttribute')
            ->will($this->returnSelf());
        $this->customerRepository->expects($this->any())->method('get')
            ->will($this->returnValue($customer));

        $this->resultJson->expects($this->once())->method('setData')->willReturn(
            [
                'status' => 'error',
                'message' => 'A user with this email address already exists in the system. '
                    . 'Enter a different email address to create this user.',
                'data' => []
            ]
        );
        $this->check->execute();
    }

    /**
     * function testExecuteWithErrorCustomerInCompany.
     *
     * @return void
     */
    public function testExecuteWithErrorCustomerInCompany()
    {
        $companyAttributes = $this->createMock(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        $companyAttributes->expects($this->any())->method('getCompanyId')
            ->will($this->returnValue(1));

        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $customerExtension->expects($this->any())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        $customer = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $customer->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
        $customer->expects($this->any())->method('setCustomAttribute')
            ->will($this->returnSelf());
        $this->customerRepository->expects($this->any())->method('get')
            ->will($this->returnValue($customer));

        $this->resultJson->expects($this->once())->method('setData')->willReturn(
            [
                'status' => 'error',
                'message' => 'A user with this email address is already a member of your company.',
                'data' => []
            ]
        );
        $this->check->execute();
    }

    /**
     * function testExecuteWithErrorCustomerFree.
     *
     * @return void
     */
    public function testExecuteWithErrorCustomerFree()
    {
        $companyAttributes = $this->createMock(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        $companyAttributes->expects($this->any())->method('getCompanyId')
            ->will($this->returnValue(0));
        $companyAttributes->expects($this->any())->method('getJobTitle')
            ->will($this->returnValue('job'));
        $companyAttributes->expects($this->any())->method('getTelephone')
            ->will($this->returnValue('111'));
        $companyAttributes->expects($this->any())->method('getStatus')
            ->will($this->returnValue('1'));

        $customerExtension = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $customerExtension->expects($this->any())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        $customer = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $customer->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
        $customer->expects($this->any())->method('getFirstname')->willReturn('first');
        $customer->expects($this->any())->method('getLastname')->willReturn('last');
        $customer->expects($this->any())->method('setCustomAttribute')
            ->will($this->returnSelf());
        $this->customerRepository->expects($this->any())->method('get')
            ->will($this->returnValue($customer));

        $this->resultJson->expects($this->once())->method('setData')->willReturn(
            [
                'status' => 'ok',
                'message' => 'A user with this email address already exists in the system. '
                    . 'If you proceed, the user will be linked to your company.',
                'data' => [
                    'firstname' => 'first',
                    'lastname' => 'last',
                    'customer[jobtitle]' => 'job',
                    'customer[telephone]' => '111',
                    'customer[status]' => '1',
                ]
            ]
        );
        $this->check->execute();
    }
}
