<?php
namespace Magento\Support\Test\Unit\Model\Backup;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Support\Model\Backup\Status
     */
    protected $status;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \Magento\Support\Model\DataFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataFormatterMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataFormatterMock = $this->createMock(\Magento\Support\Model\DataFormatter::class);
        $this->dataHelperMock = $this->createMock(\Magento\Backend\Helper\Data::class);

        $this->status = $this->objectManagerHelper->getObject(
            \Magento\Support\Model\Backup\Status::class,
            [
                'dataHelper' => $this->dataHelperMock,
                'dataFormatter' => $this->dataFormatterMock
            ]
        );
    }

    /**
     * @param int $status
     * @param array $expectedResult
     * @return void
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($status, $expectedResult)
    {
        $backupId = 1;
        $type = 1;
        $params = ['backup_id' => $backupId, 'type' => $type];

        $item = $this->getAbstractItem();
        $item->expects($this->once())
            ->method('getBackupId')
            ->willReturn($backupId);
        $item->expects($this->once())
            ->method('getType')
            ->willReturn($type);
        $item->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);

        $this->dataHelperMock->expects($this->once())
            ->method('getUrl')
            ->with('support/backup/download', $params)
            ->willReturn('http://localhost/some_link');

        $this->assertEquals($expectedResult, $this->status->getValue($item));
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            [
                'status' => \Magento\Support\Model\Backup\AbstractItem::STATUS_PROCESSING,
                'expectedResult' => ['isLink' => 0, 'link' => __('Processing ...')]
            ],
            [
                'status' => \Magento\Support\Model\Backup\AbstractItem::STATUS_COMPLETE,
                'expectedResult' => ['isLink' => 1, 'link' => 'http://localhost/some_link']
            ],
            [
                'status' => -1,
                'expectedResult' => ['isLink' => 0, 'link' => __('Unknown Status')]
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetCodeDumpLabel()
    {
        $name = 'code name';
        $item = $this->getAbstractItem();
        $item->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $this->assertSame($name, $this->status->getCodeDumpLabel($item));
    }

    /**
     * @return void
     */
    public function testGetDbDumpLabel()
    {
        $name = 'db name';
        $item = $this->getAbstractItem();
        $item->expects($this->once())
            ->method('getDbName')
            ->willReturn($name);

        $this->assertSame($name, $this->status->getDbDumpLabel($item));
    }

    /**
     * @return void
     */
    public function testGetSize()
    {
        $size = 10;
        $formattedSize = '10Mb';

        $item = $this->getAbstractItem();
        $item->expects($this->once())
            ->method('getSize')
            ->willReturn($size);
        $this->dataFormatterMock->expects($this->once())
            ->method('formatBytes')
            ->with($size)
            ->willReturn($formattedSize);

        $this->assertSame($formattedSize, $this->status->getSize($item));
    }

    /**
     * @return \Magento\Support\Model\Backup\AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAbstractItem()
    {
        $item = $this->getMockBuilder(\Magento\Support\Model\Backup\AbstractItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getBackupId', 'getType', 'getName', 'getDbName', 'getSize'])
            ->getMockForAbstractClass();

        return $item;
    }
}
