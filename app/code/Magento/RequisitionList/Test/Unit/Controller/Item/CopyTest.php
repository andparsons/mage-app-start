<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Item;

use \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;

/**
 * Class CopyTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestValidator;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListManagement;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemRepository;

    /**
     * @var RequisitionListItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\RequisitionList\Controller\Item\Copy
     */
    private $copy;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->requestValidator = $this->createMock(\Magento\RequisitionList\Model\Action\RequestValidator::class);
        $this->requisitionListRepository =
            $this->createMock(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class);
        $this->requisitionListManagement =
            $this->createMock(\Magento\RequisitionList\Api\RequisitionListManagementInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->requisitionListItemRepository =
            $this->createMock(\Magento\RequisitionList\Model\RequisitionList\Items::class);
        $this->requisitionListItemFactory = $this->createPartialMock(
            \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory::class,
            ['create']
        );
        $this->resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->copy = $objectManager->getObject(
            \Magento\RequisitionList\Controller\Item\Copy::class,
            [
                '_request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'requestValidator' => $this->requestValidator,
                'requisitionListRepository' => $this->requisitionListRepository,
                'requisitionListManagement' => $this->requisitionListManagement,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'requisitionListItemRepository' => $this->requisitionListItemRepository,
                'requisitionListItemFactory' => $this->requisitionListItemFactory
            ]
        );
    }

    /**
     * Test execute
     *
     * @param \Magento\Framework\Controller\ResultInterface|null $result
     * @dataProvider dataProviderExecute
     */
    public function testExecute($result)
    {
        $this->prepareMocks($result);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->copy->execute());
    }

    /**
     * Test execute with Exception
     */
    public function testExecuteWithException()
    {
        /**
         * @var \Magento\Framework\Controller\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $result
         */
        $result = $this->createMock(\Magento\Framework\Controller\ResultInterface::class);
        $this->prepareMocks($result);
        $exception = new \Exception();
        $this->requisitionListRepository->expects($this->any())->method('save')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->copy->execute());
    }

    /**
     * Test execute with NoSuchEntityException
     */
    public function testExecuteWithNoSuchEntityException()
    {
        /**
         * @var \Magento\Framework\Controller\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $result
         */
        $result = $this->createMock(\Magento\Framework\Controller\ResultInterface::class);
        $this->prepareMocks($result);
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\NoSuchEntityException($phrase);
        $this->requisitionListRepository->expects($this->any())->method('save')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->copy->execute());
    }

    /**
     * DataProvider execute
     *
     * @return array
     */
    public function dataProviderExecute()
    {
        $result = $this->createMock(\Magento\Framework\Controller\ResultInterface::class);

        return [
            [$result],
            [null]
        ];
    }

    /**
     * Prepare mocks
     *
     * @param \Magento\Framework\Controller\ResultInterface|null $result
     */
    private function prepareMocks($result)
    {
        $this->requestValidator->expects($this->any())->method('getResult')->willReturn($result);
        $resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $resultRedirect->expects($this->any())->method('setRefererUrl')->willReturnSelf();
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultRedirect);
        $this->request->expects($this->any())->method('getParam')->willReturn(1);
        $item = $this->createPartialMock(
            \Magento\Framework\Api\ExtensibleDataInterface::class,
            ['getQty', 'getOptions', 'getSku']
        );
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $searchResults->expects($this->any())->method('getItems')->willReturn([$item]);
        $this->requisitionListItemRepository->expects($this->any())->method('getList')->willReturn($searchResults);
        $this->searchCriteriaBuilder->expects($this->any())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);

        $requisitionList = $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class);
        $this->requisitionListRepository->expects($this->any())->method('get')->willReturn($requisitionList);
        $requisitionListItem = $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class);
        $this->requisitionListItemFactory->expects($this->any())->method('create')->willReturn($requisitionListItem);
    }
}
