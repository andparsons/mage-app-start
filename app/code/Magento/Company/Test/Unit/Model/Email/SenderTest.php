<?php

namespace Magento\Company\Test\Unit\Model\Email;

/**
 * Unit tests for Company/Model/Email/Sender model.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Email\Sender
     */
    private $sender;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessor;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Company\Model\Email\CustomerData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerData;

    /**
     * @var \Magento\Company\Model\Config\EmailTemplate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailTemplateConfig;

    /**
     * @var \Magento\Company\Model\Email\Transporter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transporter;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->setMethods(['getStoreIds'])->disableOriginalConstructor()->getMock();
        $this->storeManager->expects($this->any())->method('getWebsite')->willReturn($website);
        $website->expects($this->any())->method('getStoreIds')->willReturn([1, 2, 3]);

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->dataProcessor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()->getMock();
        $this->emailTemplateConfig = $this->getMockBuilder(\Magento\Company\Model\Config\EmailTemplate::class)
            ->disableOriginalConstructor()->getMock();
        $this->dataProcessor->expects($this->any())
            ->method('buildOutputDataArray')->willReturn([]);
        $this->customerViewHelper = $this->getMockBuilder(\Magento\Customer\Api\CustomerNameGenerationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->transporter = $this->getMockBuilder(\Magento\Company\Model\Email\Transporter::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $companyModel = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods(['getName', 'getSalesRepresentativeId'])
            ->getMockForAbstractClass();
        $companyModel->expects($this->any())->method('getName')->willReturn('Company Name');
        $companyModel->expects($this->any())->method('getSalesRepresentativeId')->willReturn(1);

        $this->customerData = $this->getMockBuilder(\Magento\Company\Model\Email\CustomerData::class)
            ->disableOriginalConstructor()->getMock();
        $customerData = new \Magento\Framework\DataObject(['email' => 'example@example.com', 'name' => 'test']);
        $this->customerData->expects($this->any())->method('getDataObjectByCustomer')->willReturn($customerData);
        $this->customerData->expects($this->any())->method('getDataObjectSuperUser')->willReturn($customerData);

        $salesRepData = new \Magento\Framework\DataObject(['email' => 'salesrep@example.com', 'name' => 'test']);
        $this->customerData->expects($this->any())
            ->method('getDataObjectSalesRepresentative')->willReturn($salesRepData);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sender = $objectManagerHelper->getObject(
            \Magento\Company\Model\Email\Sender::class,
            [
                'storeManager' => $this->storeManager,
                'scopeConfig' => $this->scopeConfig,
                'transporter' => $this->transporter,
                'customerViewHelper' => $this->customerViewHelper,
                'customerData' => $this->customerData,
                'emailTemplateConfig' => $this->emailTemplateConfig,
                'companyRepository' => $this->companyRepository
            ]
        );
    }

    /**
     * Test sendAssignSuperUserNotificationEmail.
     *
     * @return void
     */
    public function testSendAssignSuperUserNotificationEmail()
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getName', 'getEmail', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->any())->method('getStoreId')->willReturn(0);
        $customer->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $customer->expects($this->any())->method('getEmail')->willReturn('example@example.com');

        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($company);

        $this->customerViewHelper->expects($this->any())->method('getCustomerName')->willReturn('test');
        $this->transporter->expects($this->atLeastOnce())->method('sendMessage')->withConsecutive(
            ['salesrep@example.com', 'test'],
            ['example@example.com', 'test']
        );

        $this->assertEquals($this->sender, $this->sender->sendAssignSuperUserNotificationEmail($customer, 1));
    }

    /**
     * Test sendSalesRepresentativeNotificationEmail.
     *
     * @return void
     */
    public function testSendSalesRepresentativeNotificationEmail()
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getName', 'getEmail', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->any())->method('getStoreId')->willReturn(0);
        $customer->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $customer->expects($this->any())->method('getName')->willReturn('test');
        $customer->expects($this->any())->method('getEmail')->willReturn('salesrep@example.com');
        $customer->expects($this->any())->method('load')->will($this->returnSelf());
        $this->transporter->expects($this->once())->method('sendMessage')->with('salesrep@example.com', 'test');

        $this->assertEquals($this->sender, $this->sender->sendSalesRepresentativeNotificationEmail(1, 2));
    }

    /**
     * Test sendCustomerCompanyAssignNotificationEmail.
     *
     * @return void
     */
    public function testSendCustomerCompanyAssignNotificationEmail()
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getName', 'getEmail', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->any())->method('getStoreId')->willReturn(0);
        $customer->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $customer->expects($this->any())->method('getEmail')->willReturn('example@example.com');
        $this->customerViewHelper->expects($this->any())->method('getCustomerName')->willReturn('test');
        $this->transporter->expects($this->once())->method('sendMessage')->with('example@example.com', 'test');

        $this->assertEquals($this->sender, $this->sender->sendCustomerCompanyAssignNotificationEmail($customer, 2));
    }

    /**
     * Test sendRemoveSuperUserNotificationEmail.
     *
     * @return void
     */
    public function testSendRemoveSuperUserNotificationEmail()
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getName', 'getEmail', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->once())->method('getStoreId')->willReturn(0);
        $customer->expects($this->exactly(2))->method('getWebsiteId')->willReturn(2);
        $customer->expects($this->once())->method('getEmail')->willReturn('example@example.com');
        $this->customerViewHelper->expects($this->once())->method('getCustomerName')->willReturn('test');
        $this->transporter->expects($this->once())->method('sendMessage')->with('example@example.com', 'test');

        $this->assertEquals($this->sender, $this->sender->sendRemoveSuperUserNotificationEmail($customer, 2));
    }

    /**
     * Test sendInactivateSuperUserNotificationEmail.
     *
     * @return void
     */
    public function testSendInactivateSuperUserNotificationEmail()
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getName', 'getEmail', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->once())->method('getStoreId')->willReturn(0);
        $customer->expects($this->exactly(2))->method('getWebsiteId')->willReturn(2);
        $customer->expects($this->once())->method('getEmail')->willReturn('example@example.com');
        $this->customerViewHelper->expects($this->once())->method('getCustomerName')->willReturn('test');
        $this->transporter->expects($this->once())->method('sendMessage')->with('example@example.com', 'test');

        $this->assertEquals($this->sender, $this->sender->sendInactivateSuperUserNotificationEmail($customer, 2));
    }

    /**
     * Test sendCompanyStatusChangeNotificationEmail.
     *
     * @return void
     */
    public function testSendCompanyStatusChangeNotificationEmail()
    {
        $template = 'company/email/company_status_pending_approval_to_active_template';
        $customerEmailIdentity = 'customer/create_account/email_identity';
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getName', 'getEmail', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->once())->method('getStoreId')->willReturn(0);
        $customer->expects($this->exactly(2))->method('getWebsiteId')->willReturn(2);
        $customer->expects($this->once())->method('getEmail')->willReturn('example@example.com');
        $this->scopeConfig->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [$template, 'store', 1],
                [$customerEmailIdentity, 'store', 1]
            )
            ->willReturnOnConsecutiveCalls(
                'template',
                'example@example.com'
            );
        $this->customerViewHelper->expects($this->exactly(1))->method('getCustomerName')->willReturn('test');
        $this->transporter->expects($this->exactly(1))->method('sendMessage');

        $this->assertEquals(
            $this->sender,
            $this->sender->sendCompanyStatusChangeNotificationEmail($customer, 2, $template)
        );
    }

    /**
     * Test sendAdminNotificationEmail.
     *
     * @return void
     */
    public function testSendAdminNotificationEmail()
    {
        $customer = $this->mockCustomer();
        $this->transporter->expects($this->once())->method('sendMessage');

        $this->assertEquals(
            $this->sender,
            $this->sender->sendAdminNotificationEmail($customer, 'Test Company', 'http://example.com')
        );
    }

    /**
     * Mock customer.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function mockCustomer()
    {
        $pathFirst = 'trans_email/ident_/email';
        $pathSecond = 'trans_email/ident_/name';
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->scopeConfig->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive([$pathFirst], [$pathSecond])
            ->willReturnOnConsecutiveCalls('example@example.com', 'test1 test2');
        $this->emailTemplateConfig->expects($this->once())->method('getCompanyCreateNotifyAdminTemplateId');
        $customer->expects($this->once())->method('getEmail')->willReturn('example@example.com');
        $this->customerViewHelper->expects($this->once())->method('getCustomerName')->willReturn('test1 test2');
        $customer->expects($this->once())->method('getFirstname')->willReturn('test1');

        return $customer;
    }

    /**
     * Test sendUserStatusChangeNotificationEmail.
     *
     * @param int $customerStatus
     * @param string $method
     * @param string $template
     * @dataProvider sendUserStatusChangeNotificationEmailDataProvider
     * @return void
     */
    public function testSendUserStatusChangeNotificationEmail($customerStatus, $method, $template)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getName', 'getEmail', 'load'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->emailTemplateConfig->expects($this->once())->method($method)->willReturn($template);
        $customer->expects($this->once())->method('getEmail')->willReturn('example@example.com');
        $this->customerViewHelper->expects($this->once())->method('getCustomerName')->willReturn('test1 test2');
        $this->transporter->expects($this->once())->method('sendMessage')->with('example@example.com', 'test1 test2');
        $this->sender->sendUserStatusChangeNotificationEmail($customer, $customerStatus);
    }

    /**
     * Data provider for testSendUserStatusChangeNotificationEmail.
     *
     * @return array
     */
    public function sendUserStatusChangeNotificationEmailDataProvider()
    {
        return [
            [1, 'getActivateCustomerTemplateId', 'customer/customer_change_status/email_activate_customer_template'],
            [0, 'getInactivateCustomerTemplateId', 'customer/customer_change_status/email_lock_customer_template']
        ];
    }
}
