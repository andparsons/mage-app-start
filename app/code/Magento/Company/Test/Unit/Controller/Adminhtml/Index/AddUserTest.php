<?php
namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Controller\Result\Json as JsonResult;

/**
 * Unit tests for Magento\Company\Controller\Adminhtml\Index\AddUser class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Company\Controller\Adminhtml\Index\AddUser
     */
    private $addUser;

    /**
     * @var \Magento\Company\Model\CustomerRetriever|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRetrieverMock;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagementMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * Setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->customerRetrieverMock = $this->getMockBuilder(\Magento\Company\Model\CustomerRetriever::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyManagementMock = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->addUser = $this->objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Index\AddUser::class,
            [
                '_request' => $this->request,
                'customerRetriever' => $this->customerRetrieverMock,
                'companyManagement' => $this->companyManagementMock,
                'logger' => $this->loggerMock,
                'resultFactory' => $this->resultFactoryMock,
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->prepareResponseMock();
        $email = 'test@test.com';
        $websiteId = 2;
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['email'], ['website_id'])
            ->willReturnOnConsecutiveCalls($email, $websiteId);
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Model\Customer\CompanyAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['getJobTitle', 'getStatus'])
            ->getMock();

        $companyAttributes->expects($this->once())->method('getJobTitle')->willReturn('job title');
        $customerExtension = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $customerExtension->expects($this->atLeastOnce())->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($customerExtension);
        $this->customerRetrieverMock
            ->expects($this->once())
            ->method('retrieveForWebsite')
            ->with($email, $websiteId)
            ->willReturn($customer);
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyManagementMock->expects($this->once())->method('getByCustomerId')->willReturn($company);

        $this->assertInstanceOf(JsonResult::class, $this->addUser->execute());
    }

    /**
     * Test execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->prepareResponseMock();
        $exception = new \Exception();
        $this->request->expects($this->once())->method('getParam')->with('email')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->addUser->execute());
    }

    /**
     * Test execute with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $this->prepareResponseMock();
        $email = 'test';
        $this->request->expects($this->once())->method('getParam')->with('email')->willReturn($email);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->addUser->execute());
    }

    /**
     * Test execute with NoSuchEntityException.
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $this->prepareResponseMock();
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\NoSuchEntityException($phrase);
        $this->request->expects($this->once())->method('getParam')->with('email')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->addUser->execute());
    }

    /**
     * Prepare response mock.
     *
     * @return void
     */
    private function prepareResponseMock()
    {
        $responseMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($responseMock);
    }
}
