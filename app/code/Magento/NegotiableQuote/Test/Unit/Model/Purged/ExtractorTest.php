<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Purged;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Extractor model.
 */
class ExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Extractor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extractor;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerNameGenerator;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerNameGenerator = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerNameGenerationInterface::class)
            ->setMethods(['getCustomerName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyManagement = $this->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->setMethods(['getSalesRepresentative', 'getAdminByCompanyId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->extractor = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\Purged\Extractor::class,
            [
                'customerNameGenerator' => $this->customerNameGenerator,
                'companyRepository' => $this->companyRepository,
                'companyManagement' => $this->companyManagement
            ]
        );
    }

    /**
     * Test extractCustomer method.
     *
     * @param int|null $salesRepresentativeId
     * @param string|null $salesRepName
     * @param array $calls
     * @dataProvider extractCustomerDataProvider
     * @return void
     */
    public function testExtractCustomer($salesRepresentativeId, $salesRepName, array $calls)
    {
        $customerName = 'Test Customer';
        $this->customerNameGenerator->expects($this->once())->method('getCustomerName')->willReturn($customerName);
        $companyId = 23;
        $groupId = 4;
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $user = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $user->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $companyName = 'Test Company';
        $companyEmail = 'test_company@test.com';
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $company->expects($this->exactly($calls['getSalesRepresentativeId']))
            ->method('getSalesRepresentativeId')
            ->willReturn($salesRepresentativeId);
        $this->companyRepository->expects($this->once())->method('get')->willReturn($company);
        $this->companyManagement->expects($this->exactly($calls['getSalesRepresentative']))
            ->method('getSalesRepresentative')
            ->willReturn($salesRepName);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getEmail'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyManagement->expects($this->once())
            ->method('getAdminByCompanyId')
            ->willReturn($customer);
        $customer->expects($this->once())
            ->method('getEmail')
            ->willReturn($companyEmail);
        $customer->expects($this->once())
            ->method('getGroupId')
            ->willReturn($groupId);
        $data = [
            'customer_name' => $customerName,
            \Magento\Company\Api\Data\CompanyInterface::COMPANY_ID => $companyId,
            \Magento\Company\Api\Data\CompanyInterface::NAME => $companyName,
            \Magento\Company\Api\Data\CompanyInterface::EMAIL => $companyEmail,
            \Magento\Company\Api\Data\CompanyInterface::SALES_REPRESENTATIVE_ID => $salesRepresentativeId,
            \Magento\Company\Api\Data\CompanyInterface::CUSTOMER_GROUP_ID => $groupId
        ];
        if ($salesRepName) {
            $data['sales_representative_name'] = $salesRepName;
        }
        $this->assertEquals($data, $this->extractor->extractCustomer($user));
    }

    /**
     * Data provider for extractCustomer method.
     *
     * @return array
     */
    public function extractCustomerDataProvider()
    {
        return [
            [
                23, 'Sales Rep',
                [
                    'getSalesRepresentativeId' => 3,
                    'getSalesRepresentative' => 1,
                ]
            ],
            [
                null, null,
                [
                    'getSalesRepresentativeId' => 2,
                    'getSalesRepresentative' => 0
                ]
            ]
        ];
    }

    /**
     * Test extractUser method.
     *
     * @return void
     */
    public function testExtractUser()
    {
        $hasFirstName = false;
        $userId = 34;
        $userFirstName = 'Test';
        $userLastName = 'User';
        $user = $this->getMockBuilder(\Magento\User\Api\Data\UserInterface::class)
            ->setMethods([
                'hasFirstName',
                'getId',
                'load',
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $user->expects($this->once())->method('hasFirstName')->willReturn($hasFirstName);
        $user->expects($this->once())->method('getId')->willReturn($userId);
        $user->expects($this->once())->method('load')->willReturnSelf();
        $user->expects($this->once())->method('getFirstname')->willReturn($userFirstName);
        $user->expects($this->once())->method('getLastname')->willReturn($userLastName);

        $data = [
            'sales_representative_name' => $userFirstName . ' ' . $userLastName
        ];
        $this->assertEquals($data, $this->extractor->extractUser($user));
    }
}
