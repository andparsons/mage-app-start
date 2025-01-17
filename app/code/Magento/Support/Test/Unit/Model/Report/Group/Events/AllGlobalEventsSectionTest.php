<?php
namespace Magento\Support\Test\Unit\Model\Report\Group\Events;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ConfigInterface;

class AllGlobalEventsSectionTest extends AbstractEventsSectionTest
{
    /**
     * @return void
     */
    public function testGenerate()
    {
        $this->readerMock->expects($this->once())->method('read')->with($this->getExpectedAreaCode())->willReturn([]);
        $expectedEmpty = [
            $this->getExpectedTitle() => [
                'headers' => [(string)__('Event Name'), (string)__('Observer Class'), (string)__('Method')],
                'data' => [],
                'count' => 0,
            ],
        ];
        $this->assertSame($expectedEmpty, $this->getSection()->generate());
    }

    /**
     * @return void
     */
    public function testIsNamespaceRelatedToType()
    {
        $this->assertSame(true, $this->getSection()->isNamespaceRelatedToType('Magento', null));
        $this->assertSame(true, $this->getSection()->isNamespaceRelatedToType('Magento', ConfigInterface::TYPE_CORE));
        $this->assertSame(false, $this->getSection()->isNamespaceRelatedToType('Custom', ConfigInterface::TYPE_CORE));
        $this->assertSame(true, $this->getSection()->isNamespaceRelatedToType('Custom', ConfigInterface::TYPE_CUSTOM));
        $this->assertSame(
            false,
            $this->getSection()->isNamespaceRelatedToType('Magento', ConfigInterface::TYPE_CUSTOM)
        );
    }

    /**
     * @param array $events
     * @param string $eventName
     * @param array $observer
     * @param string $namespace
     * @param string $classPath
     * @param array $expected
     * @dataProvider getEventsDataProvider
     */
    public function testPushEvent(array $events, $eventName, array $observer, $namespace, $classPath, array $expected)
    {
        $this->assertSame(
            $expected,
            $this->getSection()->pushEvent($events, $eventName, $observer, $namespace, $classPath, $expected)
        );
    }

    /**
     * @return array
     */
    public function getEventsDataProvider()
    {
        return [
            [
                [],
                'test_event',
                [
                    'name' => 'test_observer',
                    'instance' => 'Magento/TestModule/TestObserver',
                    'method' => 'testMethod',
                ],
                'Magento',
                'app/code/Magento/TestModule/TestObserver.php',
                [
                    'test_event' => [
                        [
                            'name' => 'test_observer',
                            'instance' => 'Magento/TestModule/TestObserver'
                                . PHP_EOL . '{app/code/Magento/TestModule/TestObserver.php}',
                            'method' => 'testMethod',
                        ],
                    ]
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedType()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedTitle()
    {
        return (string)__('All Global events');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSectionName()
    {
        return \Magento\Support\Model\Report\Group\Events\AllGlobalEventsSection::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedAreaCode()
    {
        return Area::AREA_GLOBAL;
    }
}
