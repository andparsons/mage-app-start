<?php
namespace Magento\CatalogRule\Test\Unit\Block\Adminhtml\Edit;

use Magento\CatalogRule\Controller\RegistryConstants;

class GenericButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = new \Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton(
            $contextMock,
            $this->registryMock
        );
    }

    public function testCanRender()
    {
        $name = "Catalog Rule";
        $this->assertEquals($name, $this->model->canRender($name));
    }

    public function testGetUrl()
    {
        $url = "http://magento.com/catalogRule/";
        $route = 'button';
        $params = ['unit' => 'test'];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($route, $params)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getUrl($route, $params));
    }

    public function testGetRuleId()
    {
        $ruleId = 42;
        $ruleMock = new \Magento\Framework\DataObject(['id' => $ruleId]);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_CATALOG_RULE_ID)
            ->willReturn($ruleMock);

        $this->assertEquals($ruleId, $this->model->getRuleId());
    }

    public function testGetRuleIdWithoutRule()
    {
        $this->assertNull($this->model->getRuleId());
    }
}
