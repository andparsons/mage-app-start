<?php

namespace Magento\Company\Test\Unit\Model\Action\Customer;

/**
 * Unit test for Magento\Company\Model\Action\Customer\Populator class.
 */
class PopulatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Company\Model\Action\Customer\Populator
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerFactory = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->objectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\Action\Customer\Populator::class,
            [
                'customerRepository' => $this->customerRepository,
                'customerFactory' => $this->customerFactory,
                'objectHelper' => $this->objectHelper,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * Test populate method.
     *
     * @return void
     */
    public function testPopulate()
    {
        $customerId = 1;
        $websiteId = 1;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $data = [
            'firstname' => 'CustomerFirst',
            'lastname' => 'CustomerLast',
        ];
        $this->customerFactory->expects($this->once())->method('create')->willReturn($customer);
        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $this->objectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($customer, $data, \Magento\Customer\Api\Data\CustomerInterface::class)
            ->willReturnSelf();
        $this->storeManager->expects($this->once())->method('getWebsite')->willReturn($website);
        $website->expects($this->once())->method('getId')->willReturn($websiteId);
        $customer->expects($this->once())->method('setWebsiteId')->willReturn($customerId);
        $customer->expects($this->once())->method('setId')->with($customerId)->willReturnSelf();

        $this->model->populate($data);
    }
}
