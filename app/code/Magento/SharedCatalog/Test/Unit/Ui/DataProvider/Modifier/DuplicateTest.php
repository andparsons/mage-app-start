<?php
namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Test for duplicate modifier.
 */
class DuplicateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Modifier\Duplicate
     */
    private $modifier;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modifier = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Modifier\Duplicate::class,
            [
                'request' => $this->request,
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test modifyData method with create action.
     *
     * @return void
     */
    public function testModifyDataCreate()
    {
        $data = [
            'items' => [
                [
                    SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => 1,
                    'catalog_details' => ['name' => 'Name']
                ]
            ],
            'config' => ['submit_url' => 'shared_catalog/sharedCatalog/save']
        ];
        $this->request->expects($this->once())->method('getActionName')->willReturn('create');
        $this->assertEquals($data, $this->modifier->modifyData($data));
    }

    /**
     * Test modifyData method with duplicate action.
     *
     * @return void
     */
    public function testModifyDataDuplicate()
    {
        $data = [
            'items' => [
                [
                    SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => 1,
                    'catalog_details' => ['name' => 'Name']
                ]
            ],
            'config' => ['submit_url' => 'shared_catalog/sharedCatalog/save']
        ];
        $expect = [
            'items' => [
                [
                    'duplicate_id' => 1,
                    'catalog_details' => [
                        'name' => 'Duplicate of Name',
                        'type' => 0,
                        'created_at' => null,
                        'customer_group_id' => null
                    ]
                ]
            ],
            'config' => ['submit_url' => 'shared_catalog/sharedCatalog/duplicatePost']
        ];
        $this->request->expects($this->once())->method('getActionName')->willReturn('duplicate');
        $this->urlBuilder->expects($this->once())->method('getUrl')->willReturnArgument(0);
        $this->assertEquals($expect, $this->modifier->modifyData($data));
    }
}
