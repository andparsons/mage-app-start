<?php

namespace Magento\Company\Test\Unit\Model\Email;

/**
 * Class CustomerDataTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\Email\CustomerData
     */
    private $customerData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessor;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\User\Api\Data\UserInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * setUp
     * @return void
     */
    protected function setUp()
    {
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getStoreIds']);
        $this->storeManager->expects($this->any())->method('getWebsite')->willReturn($website);
        $website->expects($this->any())->method('getStoreIds')->willReturn([1, 2, 3]);

        $this->scopeConfig = $this->createMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->transportBuilder = $this->createPartialMock(
            \Magento\Framework\Mail\Template\TransportBuilder::class,
            ['setFrom', 'addTo', 'getTransport']
        );
        $this->transportBuilder->expects($this->any())
            ->method('setFrom')->will($this->returnSelf());
        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);
        $this->transportBuilder->expects($this->any())
            ->method('getTransport')->will($this->returnValue($transport));

        $this->dataProcessor = $this->createMock(
            \Magento\Framework\Reflection\DataObjectProcessor::class
        );
        $this->dataProcessor->expects($this->any())
            ->method('buildOutputDataArray')->willReturn([]);
        $this->customerViewHelper = $this->createMock(
            \Magento\Customer\Api\CustomerNameGenerationInterface::class
        );

        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );

        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );
        $companyModel = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods(['getName', 'getSalesRepresentativeId'])
            ->getMockForAbstractClass();

        $companyModel->expects($this->any())->method('getName')->willReturn('Company Name');
        $companyModel->expects($this->any())->method('getSalesRepresentativeId')->willReturn(1);
        $this->companyRepository->expects($this->any())->method('get')->willReturn($companyModel);
        $this->userFactory = $this->createPartialMock(
            \Magento\User\Api\Data\UserInterfaceFactory::class,
            ['create']
        );

        $this->customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getStoreId', 'getWebsiteId', 'getEmail', 'getName', 'load'])
            ->getMockForAbstractClass();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerData = $objectManagerHelper->getObject(
            \Magento\Company\Model\Email\CustomerData::class,
            [
                'dataProcessor' => $this->dataProcessor,
                'customerViewHelper' => $this->customerViewHelper,
                'companyRepository' => $this->companyRepository,
                'userFactory' => $this->userFactory,
                'customerRepository' => $this->customerRepository,
            ]
        );
    }

    /**
     * test getDataObjectByCustomer
     * @return void
     */
    public function testGetDataObjectByCustomer()
    {
        $this->customer->expects($this->any())->method('getStoreId')->willReturn(0);
        $this->customer->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $this->customer->expects($this->any())->method('getEmail')->willReturn('example@text.com');
        $this->customerViewHelper->expects($this->any())->method('getCustomerName')->willReturn('test');

        $this->assertInstanceOf(
            \Magento\Framework\DataObject::class,
            $this->customerData->getDataObjectByCustomer($this->customer, 1)
        );
    }

    /**
     * test getDataObjectByCustomer
     * @return void
     */
    public function testGetDataObjectByCustomerEmpty()
    {
        $this->customer->expects($this->any())->method('getStoreId')->willReturn(0);
        $this->customer->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $this->customer->expects($this->any())->method('getEmail')->willReturn('example@text.com');
        $this->customerViewHelper->expects($this->any())->method('getCustomerName')->willReturn('test');

        $this->assertInstanceOf(
            \Magento\Framework\DataObject::class,
            $this->customerData->getDataObjectByCustomer($this->customer, null)
        );
    }

    /**
     * test getDataObjectSuperUser
     * @return void
     */
    public function testGetDataObjectSuperUser()
    {
        $this->customer->expects($this->any())->method('getStoreId')->willReturn(0);
        $this->customer->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $this->customer->expects($this->any())->method('getEmail')->willReturn('example@text.com');
        $this->customerViewHelper->expects($this->any())->method('getCustomerName')->willReturn('test');
        $this->customerRepository->expects($this->any())->method('getById')->willReturn($this->customer);

        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $this->customerData->getDataObjectSuperUser(1));
    }

    /**
     * test getDataObjectSalesRepresentative
     * @return void
     */
    public function testGetDataObjectSalesRepresentative()
    {
        $this->customer->expects($this->any())->method('getStoreId')->willReturn(0);
        $this->customer->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $this->customer->expects($this->any())->method('getName')->willReturn('test');
        $this->customer->expects($this->any())->method('getEmail')->willReturn('example@text.com');
        $this->customer->expects($this->any())->method('load')->will($this->returnSelf());

        $this->userFactory->expects($this->any())->method('create')->willReturn($this->customer);

        $this->assertInstanceOf(
            \Magento\Framework\DataObject::class,
            $this->customerData->getDataObjectSalesRepresentative(1, 2)
        );
    }
}
