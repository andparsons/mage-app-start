<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

/**
 * Test for model SharedCatalogBuilder.
 */
class SharedCatalogBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogFactory;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogBuilder
     */
    private $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->sharedCatalogFactory = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->builder = $objectManager->getObject(
            \Magento\SharedCatalog\Model\SharedCatalogBuilder::class,
            [
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'sharedCatalogFactory' => $this->sharedCatalogFactory,
            ]
        );
    }

    /**
     * Test method build without id.
     *
     * @return void
     */
    public function testBuildNewCatalog()
    {
        $this->sharedCatalogRepository->expects($this->never())->method('get');
        $this->sharedCatalogFactory->expects($this->once())->method('create')->willReturn($this->sharedCatalog);
        $this->builder->build();
    }

    /**
     * Test method build with id.
     *
     * @return void
     */
    public function testBuildOldCatalog()
    {
        $this->sharedCatalogRepository->expects($this->once())->method('get')->willReturn($this->sharedCatalog);
        $this->assertEquals($this->sharedCatalog, $this->builder->build(1));
    }
}
