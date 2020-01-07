<?php
namespace Magento\Company\Test\Unit\Controller\Adminhtml\Customer;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanySearchResultsInterface;
use Magento\Company\Controller\Adminhtml\Customer\CompanyList;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for Magento\Company\Controller\Adminhtml\Customer\CompanyList class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompanyList
     */
    private $companyList;

    /**
     * @var CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbHelperMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->companyRepositoryMock = $this->getMockBuilder(CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dbHelperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->companyList = (new ObjectManager($this))->getObject(
            CompanyList::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                '_request' => $this->requestMock,
                'companyRepository' => $this->companyRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'dbHelper' => $this->dbHelperMock
            ]
        );
    }

    /**
     * Test `execute` method.
     *
     * @return void
     */
    public function testExecute()
    {
        $resultMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('name')
            ->willReturn('company');

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $companyMock = $this->getMockBuilder(CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $searchResultMock = $this->getMockBuilder(CompanySearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResultMock->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$companyMock]);

        $this->companyRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $this->assertEquals($resultMock, $this->companyList->execute());
    }

    /**
     * Test `execute` method with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $resultMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultMock);

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('name')
            ->willReturn('company');

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->companyRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willThrowException(new LocalizedException(new Phrase('message')));

        $resultMock->expects($this->once())
            ->method('setData')
            ->with(['error' => 'message']);

        $this->assertEquals($resultMock, $this->companyList->execute());
    }
}
