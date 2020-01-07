<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\User\Model;

/**
 * Class UserTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Extractor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extractor;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResource;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentsHandler;

    /**
     * @var \Magento\User\Api\Data\UserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\User\Model\User
     */
    private $user;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteGrid = $this
            ->createMock(\Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::class);

        $this->extractor = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Extractor::class)
            ->setMethods(['extractUser'])
            ->disableOriginalConstructor()->getMock();

        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods([
                'addFilter',
                'create'
            ])
            ->disableOriginalConstructor()->getMock();

        $this->customerResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->setMethods(['getCustomerIdsByCompanyId'])
            ->disableOriginalConstructor()->getMock();

        $this->purgedContentsHandler = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Handler::class)
            ->setMethods(['process'])
            ->disableOriginalConstructor()->getMock();

        $this->userMock = $this->getMockBuilder(\Magento\User\Api\Data\UserInterface::class)
            ->setMethods([
                'getId',
                'hasDataChanges',
                'dataHasChangedFor'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->user = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\User\Model\User::class,
            [
                'quoteGrid' => $this->quoteGrid,
                'extractor' => $this->extractor,
                'companyRepository' => $this->companyRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'customerResource' => $this->customerResource,
                'purgedContentsHandler' => $this->purgedContentsHandler
            ]
        );
    }

    /**
     * Test aroundSave() method.
     *
     * @param int $userId
     * @param bool $hasChanges
     * @param bool $hasChangesName
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $expectCall
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave(
        $userId,
        $hasChanges,
        $hasChangesName,
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $expectCall
    ) {
        $user = $this->userMock;
        $closure = function () use ($user) {
            return $user;
        };
        $user->expects($this->any())->method('getId')->willReturn($userId);
        $user->expects($this->any())->method('hasDataChanges')->willReturn($hasChanges);
        if ($hasChanges) {
            $user->expects($this->any())->method('dataHasChangedFor')->willReturn($hasChangesName);
        }
        $this->quoteGrid->expects($expectCall)->method('refreshValue');
        $this->assertInstanceOf(\Magento\User\Api\Data\UserInterface::class, $this->user->aroundSave($user, $closure));
    }

    /**
     * Data provider for aroundSave() method.
     *
     * @return array
     */
    public function aroundSaveDataProvider()
    {
        return [
            [0, false, false, $this->never()],
            [1, false, false, $this->never()],
            [1, true, false, $this->never()],
            [1, true, true, $this->once()]
        ];
    }

    /**
     * Test beforeDelete method.
     *
     * @return void
     */
    public function testBeforeDelete()
    {
        $this->userMock->expects($this->once())->method('getId')->willReturn(27);

        $associatedCustomerData = [1,2,3];
        $this->extractor->expects($this->once())->method('extractUser')->willReturn($associatedCustomerData);

        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()->getMock();

        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->willReturn($this->searchCriteriaBuilder);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn(35);
        $companyList = [$company];

        $searchResults = $this->getMockBuilder(\Magento\Company\Api\Data\CompanySearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $searchResults->expects($this->once())->method('getItems')->willReturn($companyList);

        $this->companyRepository->expects($this->once())->method('getList')->willReturn($searchResults);

        $customerIds = [36];
        $this->customerResource->expects($this->once())->method('getCustomerIdsByCompanyId')->willReturn($customerIds);

        $this->purgedContentsHandler->expects($this->once())->method('process')->willReturn(null);

        $this->assertNull($this->user->beforeDelete($this->userMock));
    }
}
