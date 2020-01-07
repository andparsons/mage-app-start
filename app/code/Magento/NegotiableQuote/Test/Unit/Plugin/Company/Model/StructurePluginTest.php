<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Company\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class StructurePluginTest.
 */
class StructurePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Company\Model\StructurePlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structurePlugin;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryInterface;

    /**
     * @var  \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subject;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods([
                'create',
                'addFilter'
            ])
            ->disableOriginalConstructor()->getMock();

        $this->customerRepositoryInterface = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->subject = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->structurePlugin = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Plugin\Company\Model\StructurePlugin::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'customerRepositoryInterface' => $this->customerRepositoryInterface
            ]
        );
    }

    /**
     * Set Up filterExistingCustomers method.
     *
     * @return array
     */
    private function setUpFilterExistingCustomersMethod()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->searchCriteriaBuilder->expects($this->exactly(1))->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->exactly(1))->method('create')->willReturn($searchCriteria);

        $customerId = 23;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $customer->expects($this->exactly(1))->method('getId')->willReturn($customerId);
        $existingCustomers = [$customer];

        $expectedResult = [$customerId];

        $searchResults = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerSearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $searchResults->expects($this->exactly(1))->method('getItems')->willReturn($existingCustomers);
        $this->customerRepositoryInterface->expects($this->exactly(1))->method('getList')->willReturn($searchResults);

        return $expectedResult;
    }

    /**
     * Test afterGetAllowedIds method.
     *
     * @return void
     */
    public function testAfterGetAllowedIds()
    {
        $result = [
            'users' => [1]
        ];

        $expected = ['users' => $this->setUpFilterExistingCustomersMethod()];

        $methodCallResult = $this->structurePlugin->afterGetAllowedIds($this->subject, $result);
        $this->assertEquals($expected, $methodCallResult);
    }

    /**
     * Test afterGetAllowedChildrenIds method.
     *
     * @return void
     */
    public function testAfterGetAllowedChildrenIds()
    {
        $allChildrenIds = [1];

        $expected = $this->setUpFilterExistingCustomersMethod();

        $methodCallResult = $this->structurePlugin->afterGetAllowedChildrenIds($this->subject, $allChildrenIds);
        $this->assertEquals($expected, $methodCallResult);
    }
}
