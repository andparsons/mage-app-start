<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Listing\Column;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Class ActionsTest
 */
class ActionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\Column\Actions|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionsMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $processor =
            $this->createPartialMock(\Magento\Framework\View\Element\UiComponent\Processor::class, ['getProcessor']);
        $this->context->expects($this->never())->method('getProcessor')->will($this->returnValue($processor));
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->actionsMock = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\Component\Listing\Column\Actions::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'urlBuilder' => $this->urlBuilder,
                'components' => [],
                'data' => [],
                'editUrl' => ''
            ]
        );
    }

    /**
     * Test for method prepareDataSource
     */
    public function testPrepareDataSource()
    {
        $dataSource['data']['items']['item'] = [SharedCatalogInterface::SHARED_CATALOG_ID => 1, 'name' => 'test'];
        $this->actionsMock->prepareDataSource($dataSource);
    }
}
