<?php

namespace Magento\Company\Test\Unit\Controller\Account;

use \Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use \Magento\Company\Api\Data\CompanySearchResultsInterface;

/**
 * Class ValidateTest
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepositoryMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var CustomerSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSearchResultsMock;

    /**
     * @var CompanySearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companySearchResultsMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonMock;

    /**
     * @var \Magento\Company\Controller\Account\Validate
     */
    private $validate;

    /**
     * Set Up
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilderMock
            ->expects($this->any())
            ->method('addFilter')
            ->will($this->returnSelf());
        $searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->customerSearchResultsMock = $this->getMockBuilder(CustomerSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $this->customerRepositoryMock
            ->expects($this->any())
            ->method('getList')
            ->will($this->returnValue($this->customerSearchResultsMock));

        $this->companyRepositoryMock = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->companySearchResultsMock = $this->getMockBuilder(CompanySearchResultsInterface::class)
            ->getMockForAbstractClass();
        $this->companyRepositoryMock
            ->expects($this->any())
            ->method('getList')
            ->will($this->returnValue($this->companySearchResultsMock));
        $this->resultFactoryMock = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->method('create')->willReturn($this->resultJsonMock);
        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->validate = $objectManager->getObject(
            \Magento\Company\Controller\Account\Validate::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'customerRepository' => $this->customerRepositoryMock,
                'companyRepository' => $this->companyRepositoryMock,
                'resultFactory' => $this->resultFactoryMock,
                '_request' => $this->requestMock,
            ]
        );
    }

    /**
     * Test for method execute
     *
     * @param int $countCompanyEmail
     * @param string $companyEmail
     * @param int $countCustomerEmail
     * @param string $customerEmail
     * @param array $data
     * @dataProvider dataProviderExecute
     */
    public function testExecute($countCompanyEmail, $companyEmail, $countCustomerEmail, $customerEmail, $data)
    {
        $this->companySearchResultsMock
            ->expects($this->any())
            ->method('getTotalCount')
            ->willReturn($countCompanyEmail);
        $this->requestMock
            ->expects($this->at(0))
            ->method('getParam')
            ->with('company_email')
            ->willReturn($companyEmail);
        $this->customerSearchResultsMock
            ->expects($this->any())
            ->method('getTotalCount')
            ->willReturn($countCustomerEmail);
        $this->requestMock
            ->expects($this->at(1))
            ->method('getParam')
            ->with('customer_email')
            ->willReturn($customerEmail);
        $resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $resultJsonMock->setData($data);
        $this->assertEquals($resultJsonMock, $this->validate->execute());
    }

    /**
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            [0, 'company@email.com', 1, 'customer@email.com', ['company_email' => false, 'customer_email' => true]],
            [1, 'company@email.com', 1, 'customer@email.com', ['company_email' => true, 'customer_email' => true]],
            [0, 'company@email.com', 0, 'customer@email.com', ['company_email' => false, 'customer_email' => false]],
        ];
    }
}
