<?php

namespace Magento\Company\Test\Unit\Model\Plugin;

use \Magento\Company\Plugin\Customer\Api\AccountManagement;

/**
 * Class AccountManagementTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userCollection;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyFactory;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStructure;

    /**
     * @var \Magento\Company\Model\CompanyManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var AccountManagement
     */
    private $accountManagement;

    /**
     * @var \Magento\Company\Model\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyEmailSender;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Company\Model\Customer\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCompanyMock;

    /**
     * Set up
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->request = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getPost']
        );
        $userCollectionFactory = $this->createPartialMock(
            \Magento\User\Model\ResourceModel\User\CollectionFactory::class,
            ['create']
        );
        $this->companyFactory = $this->createPartialMock(
            \Magento\Company\Api\Data\CompanyInterfaceFactory::class,
            ['create']
        );
        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );
        $this->companyStructure = $this->createMock(
            \Magento\Company\Model\Company\Structure::class
        );
        $this->structureRepository = $this->createMock(
            \Magento\Company\Model\StructureRepository::class
        );
        $this->companyManagement = $this->createMock(
            \Magento\Company\Api\CompanyManagementInterface::class
        );
        $this->userCollection = $this->createPartialMock(
            \Magento\User\Model\ResourceModel\User\Collection::class,
            ['setPageSize', 'getFirstItem']
        );
        $this->customerCompanyMock = $this->createPartialMock(
            \Magento\Company\Model\Customer\Company::class,
            ['createCompany']
        );
        $userCollectionFactory->expects($this->any())->method('create')->willReturn($this->userCollection);
        $this->companyEmailSender = $this->createMock(\Magento\Company\Model\Email\Sender::class);
        $this->urlBuilder = $this->createMock(\Magento\Backend\Model\UrlInterface::class);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->accountManagement = $objectManagerHelper->getObject(
            \Magento\Company\Plugin\Customer\Api\AccountManagement::class,
            [
                'request' => $this->request,
                'userCollectionFactory' => $userCollectionFactory,
                'companyFactory' => $this->companyFactory,
                'companyRepository' => $this->companyRepository,
                'companyStructure' => $this->companyStructure,
                'companyManagement' => $this->companyManagement,
                'companyEmailSender' => $this->companyEmailSender,
                'urlBuilder' => $this->urlBuilder,
                'customerCompany' => $this->customerCompanyMock
            ]
        );
    }

    /**
     * function testAfterCreateAccount
     */
    public function testAfterCreateAccount()
    {
        $property = 'name';
        $value = 'value';
        $customerId = 666;
        $companyId = 555;
        $company = [$property => $value];

        /**
         * @var \Magento\Customer\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject $subject
         */
        $subject = $this->createMock(
            \Magento\Customer\Model\AccountManagement::class
        );
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $result
         */
        $result = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $companyDataObject = $this->createMock(
            \Magento\Company\Api\Data\CompanyInterface::class
        );

        $this->request->expects($this->any())->method('getPost')->willReturnOnConsecutiveCalls($company, null);
        $this->customerCompanyMock->expects($this->once())->method('createCompany')->willReturn($companyDataObject);
        $companyDataObject->expects($this->any())->method('getId')->willReturn($companyId);
        $result->expects($this->any())->method('getId')->willReturn($customerId);
        $this->assertSame($this->accountManagement->afterCreateAccount($subject, $result), $result);
    }

    /**
     * @dataProvider afterCreateAccountDataProvider
     * @param mixed $company
     * @return void
     */
    public function testAfterCreateAccountWithStringOrEmptyCompany($company): void
    {
        /** @var \Magento\Customer\Model\AccountManagement|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->createMock(\Magento\Customer\Model\AccountManagement::class);
        /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $result */
        $result = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $companyDataObject = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);

        $this->request->expects($this->any())
            ->method('getPost')
            ->withConsecutive(['company', null], ['job_title', null])
            ->willReturnOnConsecutiveCalls($company, null);
        $this->customerCompanyMock
            ->expects($this->never())
            ->method('createCompany')
            ->willReturn($companyDataObject);
        $companyDataObject->expects($this->never())->method('getId');

        $this->assertSame($this->accountManagement->afterCreateAccount($subject, $result), $result);
    }

    /**
     * @return array
     */
    public function afterCreateAccountDataProvider(): array
    {
        return [
            ['company' => 'name'],
            ['company' => null],
            ['company' => []],
        ];
    }
}
