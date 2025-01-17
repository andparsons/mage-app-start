<?php
namespace Magento\Customer\Test\Unit\Model\Address;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $model;

    protected function setUp()
    {
        $cacheId = 'cache_id';
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $readerMock = $this->createMock(\Magento\Customer\Model\Address\Config\Reader::class);
        $cacheMock = $this->createMock(\Magento\Framework\Config\CacheInterface::class);
        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->storeMock)
        );

        $this->addressHelperMock = $this->createMock(\Magento\Customer\Helper\Address::class);

        $cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $cacheId
        )->will(
            $this->returnValue(false)
        );

        $fixtureConfigData = require __DIR__ . '/Config/_files/formats_merged.php';

        $readerMock->expects($this->once())->method('read')->will($this->returnValue($fixtureConfigData));

        $cacheMock->expects($this->once())
            ->method('save')
            ->with(
                json_encode($fixtureConfigData),
                $cacheId
            );

        $serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $serializerMock->method('serialize')
            ->willReturn(json_encode($fixtureConfigData));
        $serializerMock->method('unserialize')
            ->willReturn($fixtureConfigData);

        $this->model = $objectManagerHelper->getObject(
            \Magento\Customer\Model\Address\Config::class,
            [
                'reader' => $readerMock,
                'cache' => $cacheMock,
                'storeManager' => $storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock,
                'cacheId' => $cacheId,
                'serializer' => $serializerMock,
                'addressHelper' => $this->addressHelperMock,
            ]
        );
    }

    public function testGetStore()
    {
        $this->assertEquals($this->storeMock, $this->model->getStore());
    }

    public function testSetStore()
    {
        $this->model->setStore($this->storeMock);
        $this->assertEquals($this->storeMock, $this->model->getStore());
    }

    public function testGetFormats()
    {
        $this->storeMock->expects($this->once())->method('getId');

        $this->scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue('someValue'));

        $rendererMock = $this->createMock(\Magento\Framework\DataObject::class);

        $this->addressHelperMock->expects(
            $this->any()
        )->method(
            'getRenderer'
        )->will(
            $this->returnValue($rendererMock)
        );

        $firstExpected = new \Magento\Framework\DataObject();
        $firstExpected->setCode(
            'format_one'
        )->setTitle(
            'format_one_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            false
        )->setRenderer(
            null
        );

        $secondExpected = new \Magento\Framework\DataObject();
        $secondExpected->setCode(
            'format_two'
        )->setTitle(
            'format_two_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            true
        )->setRenderer(
            null
        );
        $expectedResult = [$firstExpected, $secondExpected];

        $this->assertEquals($expectedResult, $this->model->getFormats());
    }
}
