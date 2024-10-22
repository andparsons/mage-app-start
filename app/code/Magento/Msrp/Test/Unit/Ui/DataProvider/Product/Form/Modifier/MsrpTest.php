<?php
namespace Magento\Msrp\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Msrp\Ui\DataProvider\Product\Form\Modifier\Msrp;
use Magento\Msrp\Model\Config as MsrpConfig;

/**
 * Class MsrpTest
 */
class MsrpTest extends AbstractModifierTest
{
    /**
     * @var MsrpConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $msrpConfigMock;

    protected function setUp()
    {
        $this->msrpConfigMock = $this->getMockBuilder(MsrpConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Msrp::class, [
            'locator' => $this->locatorMock,
            'msrpConfig' => $this->msrpConfigMock,
        ]);
    }

    public function testModifyData()
    {
        $this->assertSame([], $this->getModel()->modifyData([]));
    }

    public function testModifyMeta()
    {
        $this->assertSame([], $this->getModel()->modifyMeta([]));
    }
}
