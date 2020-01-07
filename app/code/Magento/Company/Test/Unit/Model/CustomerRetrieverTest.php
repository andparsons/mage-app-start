<?php

namespace Magento\Company\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Unit test for Magento\Company\Model\CustomerRetriever class.
 */
class CustomerRetrieverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\CustomerRetriever
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getList'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\CustomerRetriever::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'customerRepository' => $this->customerRepository
            ]
        );
    }

    /**
     * Test retrieveByEmail method.
     *
     * @param $customer
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $call
     * @param \PHPUnit\Framework\MockObject\Stub\Exception|\PHPUnit\Framework\MockObject\Stub\ReturnStub $result
     * @return void
     * @dataProvider retrieveCustomerDataProvider
     */
    public function testRetrieveByEmail($customer, $call, $result)
    {
        $email = 'customer@example.com';
        $this->customerRepository->expects($this->once())->method('get')->with($email)->will($result);
        $searchCriteria = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($call)->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($call)->method('setPageSize')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($call)->method('create')->willReturn($searchCriteria);
        $searchResults = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResults->expects($call)->method('getItems')->willReturn([$customer]);
        $this->customerRepository
            ->expects($call)
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);

        $this->assertEquals($customer, $this->model->retrieveByEmail($email));
    }

    /**
     * Data provider for retrieveCustomer method.
     *
     * @return array
     */
    public function retrieveCustomerDataProvider(): array
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        return [
            [
                $customer,
                $this->atLeastOnce(),
                new \PHPUnit\Framework\MockObject\Stub\Exception(new NoSuchEntityException()),
            ],
            [
                $customer,
                $this->never(),
                new \PHPUnit\Framework\MockObject\Stub\ReturnStub($customer),
            ],
            [
                null,
                $this->never(),
                new \PHPUnit\Framework\MockObject\Stub\ReturnStub(null),
            ],
        ];
    }

    /**
     * @covers \Magento\Company\Model\CustomerRetriever::retrieveForWebsite()
     *
     * @return void
     */
    public function testRetrieveForWebsite(): void
    {
        $email = 'test@test.com';
        $websiteId = '2';
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->customerRepository->expects($this->at(0))
            ->method('get')
            ->with($email, $websiteId)
            ->willReturn($customer);
        $this->customerRepository->expects($this->at(1))
            ->method('get')
            ->with($email, $websiteId)
            ->willThrowException(new NoSuchEntityException());

        $customerRetrieved = $this->model->retrieveForWebsite($email, $websiteId);
        $this->assertEquals($customer, $customerRetrieved);
        $this->assertNull($this->model->retrieveForWebsite($email, $websiteId));
    }
}
