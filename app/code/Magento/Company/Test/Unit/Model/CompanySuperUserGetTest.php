<?php
namespace Magento\Company\Test\Unit\Model;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Model\CompanySuperUserGet;
use Magento\Company\Model\Customer\CompanyAttributes;
use Magento\Company\Model\CustomerRetriever;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Unit tests for CompanySuperUserGet model.
 */
class CompanySuperUserGetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CompanySuperUserGet
     */
    private $companySuperUserGet;

    /**
     * @var CompanyAttributes|MockObject
     */
    private $companyAttributes;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    private $customerDataFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var AccountManagementInterface|MockObject
     */
    private $accountManagement;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customer;

    /**
     * @var CompanyCustomerInterface|MockObject
     */
    private $companyCustomer;

    /**
     * @var CustomerRetriever|MockObject
     */
    private $customerRetriever;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyAttributes = $this->getMockBuilder(CompanyAttributes::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerDataFactory = $this->getMockBuilder(
            CustomerInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountManagement = $this->getMockBuilder(AccountManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyCustomer = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRetriever = $this->getMockBuilder(CustomerRetriever::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->companySuperUserGet = $this->objectManagerHelper->getObject(
            CompanySuperUserGet::class,
            [
                'companyAttributes' => $this->companyAttributes,
                'customerRepository' => $this->customerRepository,
                'customerDataFactory' => $this->customerDataFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
                'accountManagement' => $this->accountManagement,
                'customerRetriever' => $this->customerRetriever
            ]
        );
    }

    /**
     * Test for getUserForCompanyAdmin method.
     *
     * @return void
     */
    public function testGetUserForCompanyAdmin(): void
    {
        $websiteId = '2';
        $data = [
            'email' => 'companyadmin@test.com',
            CompanyCustomerInterface::JOB_TITLE => 'Job Title',
            CustomerInterface::WEBSITE_ID => $websiteId
        ];
        $this->customerRetriever
            ->expects($this->once())
            ->method('retrieveForWebsite')
            ->with($data['email'], $websiteId)
            ->willReturn(null);
        $this->prepareMocksForGetUserForCompanyAdmin($data);
        $this->customerDataFactory->method('create')
            ->willReturn($this->customer);
        $this->customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->companyCustomer->method('getStatus')
            ->willReturn(null);
        $this->companyCustomer->method('setStatus')
            ->with(CompanyCustomerInterface::STATUS_ACTIVE)
            ->willReturnSelf();
        $this->companyCustomer->method('setStatus')
            ->with(CompanyCustomerInterface::STATUS_ACTIVE)
            ->willReturnSelf();
        $this->accountManagement->method('createAccountWithPasswordHash')
            ->with($this->customer, null)
            ->willReturn($this->customer);

        $this->assertEquals($this->customer, $this->companySuperUserGet->getUserForCompanyAdmin($data));
    }

    /**
     * Prepare mocks for testGetUserForCompanyAdmin test.
     *
     * @param array $data
     * @return void
     */
    private function prepareMocksForGetUserForCompanyAdmin($data)
    {
        $this->dataObjectHelper->method('populateWithArray')
            ->with(
                $this->customer,
                $data,
                CustomerInterface::class
            );
        $this->companyAttributes->method('getCompanyAttributesByCustomer')
            ->with($this->customer)
            ->willReturn($this->companyCustomer);
        $this->companyCustomer->method('setJobTitle')
            ->with($data[CompanyCustomerInterface::JOB_TITLE])
            ->willReturnSelf();
    }

    /**
     * Test for getUserForCompanyAdmin method when customer has ID.
     *
     * @return void
     */
    public function testGetUserForCompanyAdminCustomerHasId(): void
    {
        $websiteId = '2';
        $data = [
            'email' => 'companyadmin@test.com',
            CompanyCustomerInterface::JOB_TITLE => 'Job Title',
            CustomerInterface::WEBSITE_ID => $websiteId
        ];
        $this->customerRetriever->method('retrieveForWebsite')
            ->with($data['email'], $websiteId)
            ->willReturn($this->customer);
        $this->prepareMocksForGetUserForCompanyAdmin($data);
        $this->customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->companyCustomer->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn('dummy status');
        $this->customerRepository->method('save')
            ->with($this->customer)
            ->willReturn($this->customer);

        $this->assertEquals($this->customer, $this->companySuperUserGet->getUserForCompanyAdmin($data));
    }

    /**
     * Test getUserForCompanyAdmin method when LocalizedException is thrown.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage No company admin email is specified in request.
     * @return void
     */
    public function testGetUserForCompanyAdminWithLocalizedException()
    {
        $data = [];
        $this->companySuperUserGet->getUserForCompanyAdmin($data);
    }

    /**
     * Test getUserForCompanyAdmin method when LocalizedException is thrown if no website Id is specified.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage No company admin website ID is specified in request.
     * @return void
     */
    public function testGetUserForCompanyAdminWithNoWebsiteIdException(): void
    {
        $data = ['email' => 'test@magento.com'];
        $this->companySuperUserGet->getUserForCompanyAdmin($data);
    }
}
