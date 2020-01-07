<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog;

/**
 * Test for class SharedCatalogWizardData.
 */
class SharedCatalogWizardDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\SharedCatalogWizardData
     */
    private $builder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->builder = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\SharedCatalogWizardData::class,
            [
                'request' => $this->request,
            ]
        );
    }

    /**
     * Test method populateDataFromRequest.
     *
     * @param int|null $sharedCatalogId
     * @param int $setIdCounter
     * @return void
     * @dataProvider populateDataFromRequestDataProvider
     */
    public function testPopulateDataFromRequest($sharedCatalogId, $setIdCounter)
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request
            ->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['catalog_details'], ['shared_catalog_id'])
            ->willReturnOnConsecutiveCalls(['name' => 'test'], $sharedCatalogId);
        $sharedCatalog->expects($this->once())->method('setData');
        $sharedCatalog->expects($this->exactly($setIdCounter))->method('setId');
        $this->builder->populateDataFromRequest($sharedCatalog);
    }

    /**
     * Data provider for populateDataFromRequest method.
     *
     * @return array
     */
    public function populateDataFromRequestDataProvider()
    {
        return [
            [2, 1],
            [null, 0]
        ];
    }

    /**
     * Test method populateDataFromRequest with exception.
     *
     * @return void
     * @expectedException \UnexpectedValueException
     */
    public function testPopulateDataFromRequestWithException()
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())->method('getParam')->with('catalog_details')
            ->willReturn(['test']);
        $sharedCatalog->expects($this->never())->method('setData');
        $sharedCatalog->expects($this->never())->method('setId');
        $this->builder->populateDataFromRequest($sharedCatalog);
    }
}
