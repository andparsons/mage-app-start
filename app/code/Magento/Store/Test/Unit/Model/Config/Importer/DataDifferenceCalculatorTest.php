<?php
namespace Magento\Store\Test\Unit\Model\Config\Importer;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\ScopeInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class DataDifferenceCalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataDifferenceCalculator
     */
    private $model;

    /**
     * @var ConfigSourceInterface|Mock
     */
    private $runtimeConfigSourceMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->runtimeConfigSourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();

        $this->model = new DataDifferenceCalculator(
            $this->runtimeConfigSourceMock
        );
    }

    public function testGetItemsToDelete()
    {
        $expectedData = [];
        $data = [
            'test' => [
                'code' => 'test',
                'name' => 'Test',
            ]
        ];

        $this->runtimeConfigSourceMock->expects($this->once())
            ->method('get')
            ->willReturn([
                ScopeInterface::SCOPE_GROUPS => $data
            ]);

        $this->assertSame(
            $expectedData,
            $this->model->getItemsToDelete(ScopeInterface::SCOPE_GROUPS, $data)
        );
    }

    public function testGetItemsToCreate()
    {
        $expectedData = [
            'test' => [
                'code' => 'test',
                'name' => 'Test'
            ]
        ];
        $data = [
            2 => [
                'code' => 'test',
                'name' => 'Test'
            ]
        ];

        $this->runtimeConfigSourceMock->expects($this->once())
            ->method('get')
            ->willReturn([
                ScopeInterface::SCOPE_GROUPS => []
            ]);

        $this->assertSame(
            $expectedData,
            $this->model->getItemsToCreate(ScopeInterface::SCOPE_GROUPS, $data)
        );
    }

    public function testGetItemsToUpdate()
    {
        $expectedData = [
            'test' => [
                'code' => 'test',
                'name' => 'Test2',
                'website_id' => '0',
                'default_store_id' => '0',
                'root_category_id' => '0'
            ]
        ];
        $data = [
            2 => [
                'code' => 'test',
                'name' => 'Test2'
            ]
        ];

        $this->runtimeConfigSourceMock->expects($this->once())
            ->method('get')
            ->willReturn([
                ScopeInterface::SCOPE_GROUPS => [
                    2 => [
                        'code' => 'test',
                        'name' => 'Test'
                    ]
                ]
            ]);

        $this->assertSame(
            $expectedData,
            $this->model->getItemsToUpdate(ScopeInterface::SCOPE_GROUPS, $data)
        );
    }
}
