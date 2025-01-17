<?php
namespace Magento\OfflineShipping\Test\Unit\Model\Config\Backend;

class TablerateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Config\Backend\Tablerate
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tableateFactoryMock;

    protected function setUp()
    {
        $this->tableateFactoryMock =
            $this->getMockBuilder(\Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\OfflineShipping\Model\Config\Backend\Tablerate::class,
            ['tablerateFactory' => $this->tableateFactoryMock]
        );
    }

    public function testAfterSave()
    {
        $tablerateMock = $this->getMockBuilder(\Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate::class)
            ->disableOriginalConstructor()
            ->setMethods(['uploadAndImport'])
            ->getMock();

        $this->tableateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($tablerateMock);

        $tablerateMock->expects($this->once())
            ->method('uploadAndImport')
            ->with($this->model);

        $this->model->afterSave();
    }
}
