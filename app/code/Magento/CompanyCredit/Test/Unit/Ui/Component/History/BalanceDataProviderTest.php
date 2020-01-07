<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\History;

/**
 * Class DataProviderTest.
 */
class BalanceDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\History\Collection
     */
    private $collection;

    /**
     * @var \Magento\CompanyCredit\Ui\Component\History\DataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->customerProvider = $this->collection = $this->createMock(
            \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider::class
        );
        $this->collection = $this->createMock(
            \Magento\CompanyCredit\Model\ResourceModel\History\Collection::class
        );
        $collectionFactory = $this->createPartialMock(
            \Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory::class,
            ['create']
        );
        $collectionFactory->expects($this->once())->method('create')->willReturn($this->collection);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\History\BalanceDataProvider::class,
            [
                'request' => $this->request,
                'collectionFactory' => $collectionFactory,
                'customerProvider' => $this->customerProvider
            ]
        );
    }

    /**
     * Test for getData method.
     *
     * @return void
     */
    public function testGetData()
    {
        $currentUserCredit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class);
        $userCreditInvocation = 2;
        $creditId = 1;
        $result = ['collection data'];

        $this->request->expects($this->never())->method('getParam');
        $this->customerProvider->expects($this->exactly($userCreditInvocation))->method('getCurrentUserCredit')
            ->willReturn($currentUserCredit);
        $currentUserCredit->expects($this->once())->method('getId')->willReturn($creditId);
        $this->collection->expects($this->once())->method('addFieldToFilter')
            ->with('main_table.company_credit_id', ['eq' => $creditId])->willReturnSelf();
        $this->collection->expects($this->once())->method('toArray')->willReturn($result);
        $this->assertEquals($result, $this->dataProvider->getData());
    }

    /**
     * Test for getData method with unknown user.
     *
     * @return void
     */
    public function testGetDataForUnknownUser()
    {
        $currentUserCredit = null;
        $userCreditInvocation = 1;
        $creditId = 0;
        $result = ['collection data'];

        $this->request->expects($this->never())->method('getParam');
        $this->customerProvider->expects($this->exactly($userCreditInvocation))->method('getCurrentUserCredit')
            ->willReturn($currentUserCredit);
        $this->collection->expects($this->once())->method('addFieldToFilter')
            ->with('main_table.company_credit_id', ['eq' => $creditId])->willReturnSelf();
        $this->collection->expects($this->once())->method('toArray')->willReturn($result);
        $this->assertEquals($result, $this->dataProvider->getData());
    }
}
