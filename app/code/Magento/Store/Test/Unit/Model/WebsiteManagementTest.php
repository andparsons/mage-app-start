<?php
namespace Magento\Store\Test\Unit\Model;

class WebsiteManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\WebsiteManagement
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websitesFactoryMock;

    protected function setUp()
    {
        $this->websitesFactoryMock = $this->createPartialMock(
            \Magento\Store\Model\ResourceModel\Website\CollectionFactory::class,
            ['create']
        );
        $this->model = new \Magento\Store\Model\WebsiteManagement(
            $this->websitesFactoryMock
        );
    }

    public function testGetCount()
    {
        $websitesMock = $this->createMock(\Magento\Store\Model\ResourceModel\Website\Collection::class);

        $this->websitesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($websitesMock);
        $websitesMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount()
        );
    }
}
