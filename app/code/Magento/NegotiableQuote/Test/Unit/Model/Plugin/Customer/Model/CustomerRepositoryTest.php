<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Customer\Model;

/**
 * Class CustomerRepositoryTest.
 */
class CustomerRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteGrid;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Extractor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extractor;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentsHandler;

    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Customer\Model\CustomerRepository
     */
    private $customerRepository;

    /**
     * SetUp.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteGrid = $this->createMock(\Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::class);
        $this->customerViewHelper =
            $this->createPartialMock(\Magento\Customer\Api\CustomerNameGenerationInterface::class, ['getCustomerName']);
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->extractor = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Extractor::class)
            ->setMethods(['extractCustomer'])
            ->disableOriginalConstructor()->getMock();

        $this->purgedContentsHandler = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Handler::class)
            ->setMethods(['process'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerRepository = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\Customer\Model\CustomerRepository::class,
            [
                'quoteGrid' => $this->quoteGrid,
                'customerViewHelper' => $this->customerViewHelper,
                'customerRepository' => $this->customerRepositoryMock,
                'extractor' => $this->extractor,
                'purgedContentsHandler' => $this->purgedContentsHandler
            ]
        );
    }

    /**
     * Test around save.
     *
     * @return void
     */
    public function testAroundSave()
    {
        $this->customerViewHelper->expects($this->at(0))->method('getCustomerName')->willReturn('Name');
        $this->customerViewHelper->expects($this->at(1))->method('getCustomerName')->willReturn('New Name');
        $this->customerViewHelper->expects($this->at(2))->method('getCustomerName')->willReturn('New Name');
        $oldCustomer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->any())->method('getById')->willReturn($oldCustomer);
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customer
         */
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->willReturn(1);
        $closure = function () use ($customer) {
            return $customer;
        };

        $this->assertInstanceOf(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            $this->customerRepository->aroundSave($this->customerRepositoryMock, $closure, $customer)
        );
    }

    /**
     * Test beforeDeleteById method.
     *
     * @return void
     */
    public function testBeforeDeleteById()
    {
        $customerId = 1;

        $companyId = 23;
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->setMethods(['getCompanyId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $companyAttributes->expects($this->exactly(1))->method('getCompanyId')->willReturn($companyId);

        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $extensionAttributes->expects($this->exactly(2))->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getExtensionAttributes'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $customer->expects($this->exactly(3))->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->customerRepositoryMock->expects($this->exactly(2))->method('getById')
            ->with($customerId)->willReturn($customer);

        $associatedCustomerData = [];
        $this->extractor->expects($this->exactly(1))->method('extractCustomer')->willReturn($associatedCustomerData);

        $this->purgedContentsHandler->expects($this->exactly(1))->method('process');

        $this->customerRepository->beforeDeleteById($this->customerRepositoryMock, $customerId);
    }
}
