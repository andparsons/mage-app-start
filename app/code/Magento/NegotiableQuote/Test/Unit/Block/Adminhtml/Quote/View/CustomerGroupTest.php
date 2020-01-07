<?php
namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Model\PurgedContentFactory;
use Magento\NegotiableQuote\Block\Adminhtml\Quote\View\CustomerGroup;
use Magento\Quote\Model\Quote;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;

/**
 * Unit test for \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\CustomerGroup class.
 */
class CustomerGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CustomerGroup
     */
    private $customerGroup;

    /**
     * @var CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagementMock;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepository;

    /**
     * @var PurgedContentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentFactoryMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->companyManagementMock = $this->getMockBuilder(CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->purgedContentFactoryMock = $this->getMockBuilder(PurgedContentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonSerializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods(['getCustomer'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->customerGroup = $this->objectManagerHelper->getObject(
            CustomerGroup::class,
            [
                'companyManagement' => $this->companyManagementMock,
                'groupRepository' => $this->groupRepository,
                'purgedContentFactory' => $this->purgedContentFactoryMock,
                'serializer' => $this->jsonSerializerMock,
                'quote' => $this->quoteMock,
                'urlBuilder' => $this->urlBuilder,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
            ]
        );
    }

    /**
     * Test getGroupName method.
     *
     * @return void
     */
    public function testGetGroupName()
    {
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();
        $customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($customerMock);
        $companyMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->getMockForAbstractClass();
        $this->companyManagementMock->expects($this->atLeastOnce())
            ->method('getByCustomerId')
            ->willReturn($companyMock);
        $companyMock->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        $groupMock = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->getMockForAbstractClass();
        $this->groupRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->willReturn($groupMock);

        $groupMock->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Name');

        $this->negotiableQuoteHelper->expects($this->atLeastOnce())
            ->method('resolveCurrentQuote')
            ->willReturn($this->quoteMock);

        $this->assertEquals(
            'Name',
            $this->customerGroup->getGroupName()
        );
    }

    /**
     * Test getGroupUrl method.
     *
     * @return void
     */
    public function testGetGroupUrl()
    {
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();
        $customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($customerMock);
        $companyMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->getMockForAbstractClass();
        $this->companyManagementMock->expects($this->atLeastOnce())
            ->method('getByCustomerId')
            ->willReturn($companyMock);
        $companyMock->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        $groupMock = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->getMockForAbstractClass();
        $this->groupRepository->expects($this->atLeastOnce())
            ->method('getById')
            ->willReturn($groupMock);

        $groupMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->with('customer/group/edit', [\Magento\Customer\Api\Data\GroupInterface::ID => 1])
            ->willReturnArgument(0);

        $this->negotiableQuoteHelper->expects($this->atLeastOnce())
            ->method('resolveCurrentQuote')
            ->willReturn($this->quoteMock);

        $this->assertEquals(
            'customer/group/edit',
            $this->customerGroup->getGroupUrl()
        );
    }
}
