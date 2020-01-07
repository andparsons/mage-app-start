<?php
namespace Magento\SharedCatalog\Test\Unit\Block\Adminhtml\System\Config\CategoryPermissions;

/**
 * Test for block Magento\SharedCatalog\Test\Unit\Block\Adminhtml\System\Config\CategoryPermissions\IsActive.
 */
class IsActiveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\SharedCatalog\Block\Adminhtml\System\Config\CategoryPermissions\IsActive
     */
    private $isActive;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->isActive = $objectManager->getObject(
            \Magento\SharedCatalog\Block\Adminhtml\System\Config\CategoryPermissions\IsActive::class,
            [
                '_scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * Test for render method.
     *
     * @return void
     */
    public function testRender()
    {
        $htmlId = 'htmlId';
        $elementHtml = 'Element Html';
        $element = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->setMethods(['getHtmlId', 'setDisabled', 'getElementHtml'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->scopeConfig->expects($this->atLeastOnce())
            ->method('isSetFlag')->with('btob/website_configuration/sharedcatalog_active')->willReturn(true);
        $element->expects($this->atLeastOnce())->method('getHtmlId')->willReturn($htmlId);
        $element->expects($this->once())->method('setDisabled')->with(true)->willReturnSelf();
        $element->expects($this->once())->method('getElementHtml')->willReturn($elementHtml);
        $this->assertEquals(
            sprintf(
                '<tr id="row_%1$s">'
                . '<td class="label"><label for="%1$s"><span></span></label></td>'
                . '<td class="value">%2$s</td><td class=""></td>'
                . '</tr>',
                $htmlId,
                $elementHtml
            ),
            $this->isActive->render($element)
        );
    }
}
