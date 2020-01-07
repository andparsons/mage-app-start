<?php
namespace Magento\NegotiableQuoteSharedCatalog\Test\Unit\Block\Adminhtml\Quote\View;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Model\PurgedContentFactory;
use Magento\NegotiableQuoteSharedCatalog\Block\Adminhtml\Quote\View\Info;
use Magento\Quote\Model\Quote;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\SharedCatalog\Model\SharedCatalogLocator;
use Magento\NegotiableQuote\Block\Adminhtml\Quote\View\CustomerGroup;

/**
 * Unit test for \Magento\NegotiableQuoteSharedCatalog\Block\Adminhtml\Quote\View\Info class.
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Info
     */
    private $info;

    /**
     * @var CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagementMock;

    /**
     * @var SharedCatalogLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogLocatorMock;

    /**
     * @var PurgedContentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $purgedContentFactoryMock;

    /**
     * @var DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonDecoderMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var CustomerGroup|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupBlock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->companyManagementMock = $this->getMockBuilder(CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogLocatorMock = $this->getMockBuilder(SharedCatalogLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->purgedContentFactoryMock = $this->getMockBuilder(PurgedContentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonDecoderMock = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupBlock = $this->getMockBuilder(CustomerGroup::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->info = $this->objectManagerHelper->getObject(
            Info::class,
            [
                'companyManagement' => $this->companyManagementMock,
                'sharedCatalogLocator' => $this->sharedCatalogLocatorMock,
                'purgedContentFactory' => $this->purgedContentFactoryMock,
                'jsonDecoder' => $this->jsonDecoderMock,
                'quote' => $this->quoteMock,
                'groupBlock' => $this->groupBlock,
                'urlBuilder' => $this->urlBuilder,
            ]
        );
    }

    /**
     * Test getSharedCatalogName method.
     *
     * @return void
     */
    public function testGetSharedCatalogName()
    {
        // getCustomerGroupId
        $this->groupBlock->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        // getSharedCatalog
        $sharedCatalogMock = $this->getMockBuilder(SharedCatalogInterface::class)
            ->getMockForAbstractClass();
        $this->sharedCatalogLocatorMock->expects($this->atLeastOnce())
            ->method('getSharedCatalogByCustomerGroup')
            ->willReturn($sharedCatalogMock);

        $sharedCatalogMock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('Name');

        $this->info->setQuote($this->quoteMock);

        $this->assertEquals(
            'Name',
            $this->info->getSharedCatalogName()
        );
    }

    /**
     * Test getSharedCatalogName method without shared cataog.
     *
     * @return void
     */
    public function testGetSharedCatalogNameFromCustomerGroup()
    {
        $this->groupBlock->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->groupBlock->expects($this->atLeastOnce())
            ->method('getGroupName')
            ->willReturn('Name');

        $this->sharedCatalogLocatorMock->expects($this->atLeastOnce())
            ->method('getSharedCatalogByCustomerGroup')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->info->setQuote($this->quoteMock);

        $this->assertEquals(
            'Name',
            $this->info->getSharedCatalogName()
        );
    }

    /**
     * Test getSharedCatalogUrl method.
     *
     * @return void
     */
    public function testGetSharedCatalogUrl()
    {
        // getCustomerGroupId
        $this->groupBlock->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn(1);

        // getSharedCatalog
        $sharedCatalogMock = $this->getMockBuilder(SharedCatalogInterface::class)
            ->getMockForAbstractClass();
        $this->sharedCatalogLocatorMock->expects($this->atLeastOnce())
            ->method('getSharedCatalogByCustomerGroup')
            ->willReturn($sharedCatalogMock);

        $sharedCatalogMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->info->setQuote($this->quoteMock);
        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->with('shared_catalog/sharedCatalog/edit', [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => 1])
            ->willReturnArgument(0);

        $this->assertEquals(
            'shared_catalog/sharedCatalog/edit',
            $this->info->getSharedCatalogUrl()
        );
    }

    /**
     * Test getSharedCatalogUrl method without shared cataog.
     *
     * @return void
     */
    public function testGetSharedCatalogUrlFromCustomerGroup()
    {
        $this->groupBlock->expects($this->atLeastOnce())
            ->method('getCustomerGroupId')
            ->willReturn(1);
        $this->groupBlock->expects($this->atLeastOnce())
            ->method('getGroupUrl')
            ->willReturn('group/url');

        $this->sharedCatalogLocatorMock->expects($this->atLeastOnce())
            ->method('getSharedCatalogByCustomerGroup')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->info->setQuote($this->quoteMock);

        $this->assertEquals(
            'group/url',
            $this->info->getSharedCatalogUrl()
        );
    }
}
