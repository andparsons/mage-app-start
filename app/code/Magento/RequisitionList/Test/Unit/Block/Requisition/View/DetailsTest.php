<?php

namespace Magento\RequisitionList\Test\Unit\Block\Requisition\View;

/**
 * Class DetailsTest
 */
class DetailsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\RequisitionList\Block\Requisition\View\Details
     */
    private $details;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->requisitionListRepository =
            $this->createMock(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->details = $objectManager->getObject(
            \Magento\RequisitionList\Block\Requisition\View\Details::class,
            [
                '_request' => $this->request,
                '_urlBuilder' => $this->urlBuilder,
                'requisitionListRepository' => $this->requisitionListRepository,
                'data' => []
            ]
        );
    }

    /**
     * Test getRequisitionList
     *
     * @param int $requisitionId
     * @param \Magento\RequisitionList\Api\Data\RequisitionListInterface|null
     * @dataProvider dataProviderGetRequisitionList
     */
    public function testGetRequisitionList($requisitionId, $requisitionList)
    {
        $this->request->expects($this->any())->method('getParam')->willReturn($requisitionId);
        $this->requisitionListRepository->expects($this->any())->method('get')->willReturn($requisitionList);

        $this->assertEquals($requisitionList, $this->details->getRequisitionList());
    }

    /**
     * Test getRequisitionList
     *
     * @param int $requisitionId
     * @param \Magento\RequisitionList\Api\Data\RequisitionListInterface|null
     * @param int $itemCount
     * @dataProvider dataProviderGetItemCount
     */
    public function testGetItemCount($requisitionId, $requisitionList, $itemCount)
    {
        $this->request->expects($this->any())->method('getParam')->willReturn($requisitionId);
        $this->requisitionListRepository->expects($this->any())->method('get')->willReturn($requisitionList);

        $this->assertEquals($itemCount, $this->details->getItemCount());
    }

    /**
     * Test getPrintUrl
     */
    public function testGetPrintUrl()
    {
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn('url');

        $this->assertEquals('url', $this->details->getPrintUrl());
    }

    /**
     * DataProvider getRequisitionList
     *
     * @return array
     */
    public function dataProviderGetRequisitionList()
    {
        $requisitionList = $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class);

        return [
            [null, null],
            [1, $requisitionList]
        ];
    }

    /**
     * DataProvider getItemCount
     *
     * @return array
     */
    public function dataProviderGetItemCount()
    {
        $requisitionList = $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListInterface::class);
        $requisitionList->expects($this->any())->method('getItems')->willReturn([1, 2, 3, 4, 5]);

        return [
            [null, null, 0],
            [1, $requisitionList, 5]
        ];
    }
}
