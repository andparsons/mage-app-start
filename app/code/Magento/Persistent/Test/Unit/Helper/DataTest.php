<?php
namespace Magento\Persistent\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modulesReader;

    /**
     * @var  \Magento\Persistent\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_modulesReader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $objectManager->getObject(
            \Magento\Persistent\Helper\Data::class,
            ['modulesReader' => $this->_modulesReader]
        );
    }

    public function testGetPersistentConfigFilePath()
    {
        $this->_modulesReader->expects(
            $this->once()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Persistent'
        )->will(
            $this->returnValue('path123')
        );
        $this->assertEquals('path123/persistent.xml', $this->_helper->getPersistentConfigFilePath());
    }
}
