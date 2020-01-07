<?php
namespace Magento\RequisitionList\Test\Unit\Model;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for OptionsManagement.
 */
class OptionsManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemOptionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemRepository;

    /**
     * @var RequisitionListItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Serialize\JsonValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonValidatorMock;

    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement
     */
    private $optionsManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->itemOptionFactory = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\OptionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemRepository = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionList\Items::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemFactory = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->jsonValidatorMock = $this->getMockBuilder(\Magento\Framework\Serialize\JsonValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->optionsManagement = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\OptionsManagement::class,
            [
                'itemOptionFactory' => $this->itemOptionFactory,
                'productRepository' => $this->productRepository,
                'requisitionListItemRepository' => $this->requisitionListItemRepository,
                'requisitionListItemFactory' => $this->requisitionListItemFactory,
                'serializer' => $this->serializer,
                'jsonValidator' => $this->jsonValidatorMock
            ]
        );
    }

    /**
     * Test getOptions method.
     *
     * @return void
     */
    public function testGetOptions()
    {
        $this->jsonValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $params = $this->prepareGetOptionsMocks();
        $this->assertEquals(
            $params['result'],
            $this->optionsManagement->getOptions($params['item'], $params['product'])
        );
    }

    /**
     * Test getOptions method if requisition list id is not null.
     *
     * @param int|null $itemId
     * @param int $getInvokesCount
     * @param int $createInvokesCount
     * @return void
     *
     * @dataProvider getOptionsByRequisitionListItemIdDataProvider
     */
    public function testGetOptionsByRequisitionListItemId($itemId, $getInvokesCount, $createInvokesCount)
    {
        $this->jsonValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $params = $this->prepareGetOptionsMocks();
        $item = $params['item'];
        $this->requisitionListItemRepository->expects($this->exactly($getInvokesCount))->method('get')
            ->willReturn($item);
        $this->requisitionListItemFactory->expects($this->exactly($createInvokesCount))->method('create')
            ->willReturn($item);

        $this->assertEquals(
            $params['result'],
            $this->optionsManagement->getOptionsByRequisitionListItemId($itemId, $params['product'])
        );
    }

    /**
     * Test addOption method.
     *
     * @return void
     */
    public function testAddOption()
    {
        $itemId = 1;
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $option = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData', 'getProduct', 'setProduct', 'getCode'])
            ->getMock();
        $option->expects($this->atLeastOnce())->method('getData')->willReturn([]);
        $option->expects($this->atLeastOnce())->method('setData')->willReturnSelf();
        $option->expects($this->atLeastOnce())->method('getProduct')->willReturn($product);
        $option->expects($this->atLeastOnce())->method('setProduct')->willReturnSelf();
        $option->expects($this->atLeastOnce())->method('getCode')->willReturn('code');
        $this->itemOptionFactory->expects($this->atLeastOnce())->method('create')->willReturn($option);

        $this->optionsManagement->addOption($option, $itemId);
    }

    /**
     * Test addOption method if option is an attay.
     *
     * @return void
     */
    public function testAddOptionWhenOptionIsArray()
    {
        $itemId = 1;
        $option = ['option_code' => 'option_value'];
        $optionModel = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getCode'])
            ->getMock();
        $this->itemOptionFactory->expects($this->atLeastOnce())->method('create')->willReturn($optionModel);
        $optionModel->expects($this->atLeastOnce())->method('setData')->willReturnSelf();
        $optionModel->expects($this->atLeastOnce())->method('getCode')->willReturn('option_code');

        $this->optionsManagement->addOption($option, $itemId);
    }

    /**
     * Test addOption method throes LocalizedException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testAddOptionWithLocalizedException()
    {
        $itemId = 1;
        $option = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->optionsManagement->addOption($option, $itemId);
    }

    /**
     * Test getInfoBuyRequest method.
     *
     * @return void
     */
    public function testGetInfoBuyRequest()
    {
        $options = '[{"info_buyRequest":["value"}]]';
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $item->expects($this->atLeastOnce())->method('getOptions')->willReturn(json_encode($options));
        $this->serializer->expects($this->atLeastOnce())->method('unserialize')
            ->willReturnOnConsecutiveCalls(['info_buyRequest' => ['value']], ['info_buyRequest' => ['value']]);

        $this->assertEquals(['value'], $this->optionsManagement->getInfoBuyRequest($item));
    }

    /**
     * Prepare mocks for getOptions.
     *
     * @return array
     */
    private function prepareGetOptionsMocks()
    {
        $optionId = 1;
        $options = ['simple_product' => 'value'];
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $item->expects($this->atLeastOnce())->method('getId')->willReturn($optionId);
        $item->expects($this->atLeastOnce())->method('getOptions')->willReturn(json_encode($options));
        $this->serializer->expects($this->atLeastOnce())->method('unserialize')->willReturn($options);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $option = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->atLeastOnce())->method('setData')->willReturnSelf();
        $option->expects($this->atLeastOnce())->method('setProduct')->with($product)->willReturnSelf();
        $this->itemOptionFactory->expects($this->atLeastOnce())->method('create')->willReturn($option);
        $this->productRepository->expects($this->atLeastOnce())->method('getById')->willReturn($product);

        return [
            'item' => $item,
            'product' => $product,
            'result' => ['simple_product' => $option]
        ];
    }

    /**
     * DataProvider for getOptionsByRequisitionListItemId.
     *
     * @return array
     */
    public function getOptionsByRequisitionListItemIdDataProvider()
    {
        return [
            [1, 1, 0],
            [null, 0, 1]
        ];
    }
}
