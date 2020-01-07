<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for sales representative validator.
 */
class SalesRepresentativeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userCollectionFactory;

    /**
     * @var \Magento\Company\Model\SaveValidator\SalesRepresentative
     */
    private $salesRepresentative;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->userCollectionFactory = $this
            ->getMockBuilder(\Magento\User\Model\ResourceModel\User\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->salesRepresentative = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\SalesRepresentative::class,
            [
                'company' => $this->company,
                'userCollectionFactory' => $this->userCollectionFactory,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $salesRepresentativeId = 1;
        $this->company->expects($this->atLeastOnce())
            ->method('getSalesRepresentativeId')->willReturn($salesRepresentativeId);
        $collection = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->userCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())
            ->method('addFieldToFilter')->with('main_table.user_id', $salesRepresentativeId)->willReturnSelf();
        $collection->expects($this->once())->method('load')->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn(1);
        $this->salesRepresentative->execute();
    }

    /**
     * Test for execute method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * #expectedExceptionMessage No such entity with salesRepresentativeId = 1
     */
    public function testExecuteWithException()
    {
        $salesRepresentativeId = 1;
        $this->company->expects($this->atLeastOnce())
            ->method('getSalesRepresentativeId')->willReturn($salesRepresentativeId);
        $collection = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->userCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())
            ->method('addFieldToFilter')->with('main_table.user_id', $salesRepresentativeId)->willReturnSelf();
        $collection->expects($this->once())->method('load')->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn(0);
        $this->salesRepresentative->execute();
    }
}
