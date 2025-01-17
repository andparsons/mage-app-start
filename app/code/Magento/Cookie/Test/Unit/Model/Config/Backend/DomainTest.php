<?php
namespace Magento\Cookie\Test\Unit\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\Config\Validator\CookieDomainValidator;

/**
 * Test \Magento\Cookie\Model\Config\Backend\Domain
 */
class DomainTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Model\ResourceModel\AbstractResource | \PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \Magento\Cookie\Model\Config\Backend\Domain */
    protected $domain;

    /**
     * @var  CookieDomainValidator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    protected function setUp()
    {
        $eventDispatcherMock = $this->createMock(\Magento\Framework\Event\Manager::class);
        $contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $contextMock->expects(
            $this->any()
        )->method(
            'getEventDispatcher'
        )->will(
            $this->returnValue($eventDispatcherMock)
        );

        $this->resourceMock = $this->createPartialMock(\Magento\Framework\Model\ResourceModel\AbstractResource::class, [
                '_construct',
                'getConnection',
                'getIdFieldName',
                'beginTransaction',
                'save',
                'commit',
                'addCommitCallback',
                'rollBack',
            ]);

        $this->validatorMock = $this->getMockBuilder(
            \Magento\Framework\Session\Config\Validator\CookieDomainValidator::class
        )->disableOriginalConstructor()
            ->getMock();
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->domain = $helper->getObject(
            \Magento\Cookie\Model\Config\Backend\Domain::class,
            [
                'context' => $contextMock,
                'resource' => $this->resourceMock,
                'configValidator' => $this->validatorMock,
            ]
        );
    }

    /**
     * @covers \Magento\Cookie\Model\Config\Backend\Domain::beforeSave
     * @dataProvider beforeSaveDataProvider
     *
     * @param string $value
     * @param bool $isValid
     * @param int $callNum
     * @param int $callGetMessages
     */
    public function testBeforeSave($value, $isValid, $callNum, $callGetMessages = 0)
    {
        $this->resourceMock->expects($this->any())->method('addCommitCallback')->will($this->returnSelf());
        $this->resourceMock->expects($this->any())->method('commit')->will($this->returnSelf());
        $this->resourceMock->expects($this->any())->method('rollBack')->will($this->returnSelf());

        $this->validatorMock->expects($this->exactly($callNum))
            ->method('isValid')
            ->will($this->returnValue($isValid));
        $this->validatorMock->expects($this->exactly($callGetMessages))
            ->method('getMessages')
            ->will($this->returnValue(['message']));
        $this->domain->setValue($value);
        try {
            $this->domain->beforeSave();
            if ($callGetMessages) {
                $this->fail('Failed to throw exception');
            }
        } catch (LocalizedException $e) {
            $this->assertEquals('Invalid domain name: message', $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            'not string' => [['array'], false, 1, 1],
            'invalid hostname' => ['http://', false, 1, 1],
            'valid hostname' => ['hostname.com', true, 1, 0],
            'empty string' => ['', false, 0, 0],
        ];
    }
}
