<?php
namespace Magento\Framework\MessageQueue\Test\Unit\Config\Publisher;

use Magento\Framework\MessageQueue\Config\Publisher\ConfigReaderPlugin as PublisherConfigReaderPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Publisher\Config\CompositeReader as PublisherConfigCompositeReader;

class ConfigReaderPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PublisherConfigReaderPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var PublisherConfigCompositeReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(PublisherConfigCompositeReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            PublisherConfigReaderPlugin::class,
            ['config' => $this->configMock]
        );
    }

    public function testAfterRead()
    {
        $result = ['topic0' => []];
        $binds = [
            [
                'topic' => 'topic1',
                'exchange' => 'exchange1'
            ],
            [
                'topic' => 'topic2',
                'exchange' => 'exchange2'
            ]
        ];
        $finalResult = [
            'topic1' => [
                'topic' => 'topic1',
                'connection' => [
                    'name' => 'connection1',
                    'exchange' => 'exchange1',
                    'disabled' => false
                ],
                'disabled' => false
            ],
            'topic2' => [
                'topic' => 'topic2',
                'connection' => [
                    'name' => 'connection2',
                    'exchange' => 'exchange2',
                    'disabled' => false
                ],
                'disabled' => false
            ],
            'topic0' => []
        ];

        $this->configMock->expects(static::atLeastOnce())
            ->method('getBinds')
            ->willReturn($binds);
        $this->configMock->expects(static::atLeastOnce())
            ->method('getConnectionByTopic')
            ->willReturnMap(
                [
                    ['topic1', 'connection1'],
                    ['topic2', 'connection2']
                ]
            );

        $this->assertEquals($finalResult, $this->plugin->afterRead($this->subjectMock, $result));
    }
}
