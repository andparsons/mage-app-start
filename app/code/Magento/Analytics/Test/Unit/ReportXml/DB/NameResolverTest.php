<?php
namespace Magento\Analytics\Test\Unit\ReportXml\DB;

use Magento\Analytics\ReportXml\DB\NameResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class NameResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nameResolverMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->nameResolverMock = $this->getMockBuilder(NameResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->nameResolver = $this->objectManagerHelper->getObject(NameResolver::class);
    }

    public function testGetName()
    {
        $elementConfigMock = [
            'name' => 'sales_order',
            'alias' => 'sales',
        ];

        $this->assertSame('sales_order', $this->nameResolver->getName($elementConfigMock));
    }

    /**
     * @param array $elementConfig
     * @param string|null $elementAlias
     *
     * @dataProvider getAliasDataProvider
     */
    public function testGetAlias($elementConfig, $elementAlias)
    {
        $elementName = 'elementName';

        $this->nameResolverMock
            ->expects($this->once())
            ->method('getName')
            ->with($elementConfig)
            ->willReturn($elementName);

        $this->assertSame($elementAlias ?: $elementName, $this->nameResolverMock->getAlias($elementConfig));
    }

    /**
     * @return array
     */
    public function getAliasDataProvider()
    {
        return [
            'ElementConfigWithAliases' => [
                ['alias' => 'sales', 'name' => 'sales_order'],
                'sales',
            ],
            'ElementConfigWithoutAliases' => [
                ['name' => 'sales_order'],
                null,
            ]
        ];
    }
}
