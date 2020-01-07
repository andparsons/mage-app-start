<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\CustomerGroupRetriever;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Test for class CustomerGroupRetriever.
 */
class CustomerGroupRetrieverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerGroupRetriever
     */
    private $retriever;

    /**
     * @var \Magento\Customer\Model\Group\RetrieverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupRetriever;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerGroupRetriever = $this->getMockBuilder(\Magento\Customer\Model\Group\RetrieverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteManagement = $this->getMockBuilder(NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->retriever = $objectManager->getObject(
            CustomerGroupRetriever::class,
            [
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'request' => $this->request,
                'customerGroupRetriever' => $this->customerGroupRetriever,
            ]
        );
    }

    /**
     * Test for method getCustomerGroupId with request param.
     *
     * @return void
     */
    public function testGetCustomerGroupIdRequest()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('quote_id')->willReturn(1);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->once())->method('getCustomer')->willReturn($customer);
        $customer->expects($this->once())->method('getGroupId')->willReturn(2);
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')->with(1)->willReturn($quote);

        $this->assertEquals(2, $this->retriever->getCustomerGroupId());
    }

    /**
     * Test for method getCustomerGroupId with exception.
     *
     * @return void
     */
    public function testGetCustomerGroupIdException()
    {
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('quote_id')->willReturn(1);

        $this->negotiableQuoteManagement->expects($this->once())
            ->method('getNegotiableQuote')->with(1)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->customerGroupRetriever->expects($this->once())->method('getCustomerGroupId')->willReturn(2);

        $this->assertEquals(2, $this->retriever->getCustomerGroupId());
    }

    /**
     * Test for method getCustomerGroupId without request params.
     *
     * @return void
     */
    public function testGetCustomerGroupIdWithoutRequest()
    {
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn(null);

        $this->negotiableQuoteManagement->expects($this->never())
            ->method('getNegotiableQuote');
        $this->customerGroupRetriever->expects($this->once())->method('getCustomerGroupId')->willReturn(2);

        $this->assertEquals(2, $this->retriever->getCustomerGroupId());
    }
}
