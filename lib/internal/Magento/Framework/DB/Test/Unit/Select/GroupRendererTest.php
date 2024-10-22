<?php

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;

/**
 * Class GroupRendererTest
 */
class GroupRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Select\GroupRenderer
     */
    protected $model;

    /**
     * @var \Magento\Framework\DB\Platform\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteMock = $this->createPartialMock(\Magento\Framework\DB\Platform\Quote::class, ['quoteIdentifier']);
        $this->selectMock = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['getPart']);
        $this->model = $objectManager->getObject(
            \Magento\Framework\DB\Select\GroupRenderer::class,
            ['quote' => $this->quoteMock]
        );
    }

    /**
     * @param array $mapValues
     * @dataProvider renderNoPartDataProvider
     */
    public function testRenderNoPart($mapValues)
    {
        $sql = 'SELECT';
        $this->selectMock->expects($this->any())
            ->method('getPart')
            ->willReturnMap($mapValues);
        $this->assertEquals($sql, $this->model->render($this->selectMock, $sql));
    }

    /**
     * Data provider for testRenderNoPart
     * @return array
     */
    public function renderNoPartDataProvider()
    {
        return [
            [[[Select::FROM, false], [Select::GROUP, false]]],
            [[[Select::FROM, true], [Select::GROUP, false]]],
            [[[Select::FROM, false], [Select::GROUP, true]]],
        ];
    }

    public function testRender()
    {
        $sql = 'SELECT';
        $expectedResult = $sql . ' ' . Select::SQL_GROUP_BY . ' group1' . ",\n\t" . 'group2';
        $mapValues = [
            [Select::FROM, true],
            [Select::GROUP, ['group1', 'group2']]
        ];
        $this->selectMock->expects($this->exactly(3))
            ->method('getPart')
            ->willReturnMap($mapValues);
        $this->quoteMock->expects($this->exactly(2))
            ->method('quoteIdentifier')
            ->willReturnArgument(0);
        $this->assertEquals($expectedResult, $this->model->render($this->selectMock, $sql));
    }
}
