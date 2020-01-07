<?php
namespace Magento\SharedCatalog\Test\Unit\Model\SaveHandler;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\SaveHandler\DuplicatedPublicSharedCatalog;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog as SharedCatalogResource;
use Magento\SharedCatalog\Api\ProductItemManagementInterface;
use Magento\SharedCatalog\Model\CustomerGroupManagement;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\SharedCatalog\Api\CompanyManagementInterface;
use Magento\SharedCatalog\Api\CategoryManagementInterface;

/**
 * Unit tests for DuplicatedPublicSharedCatalog save handler.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DuplicatedPublicSharedCatalogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var DuplicatedPublicSharedCatalog
     */
    private $duplicatedPublicSharedCatalog;

    /**
     * @var ProductItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductItemManagementMock;

    /**
     * @var CustomerGroupManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupManagementMock;

    /**
     * @var CatalogPermissionManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPermissionManagementMock;

    /**
     * @var CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogCompanyManagementMock;

    /**
     * @var CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryManagementMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->sharedCatalogProductItemManagementMock = $this->getMockBuilder(ProductItemManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerGroupManagementMock = $this->getMockBuilder(CustomerGroupManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogPermissionManagementMock = $this->getMockBuilder(CatalogPermissionManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogCompanyManagementMock = $this->getMockBuilder(CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryManagementMock = $this->getMockBuilder(CategoryManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->saveMock = $this->getMockBuilder(\Magento\SharedCatalog\Model\SaveHandler\SharedCatalog\Save::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->duplicatedPublicSharedCatalog = $this->objectManagerHelper->getObject(
            DuplicatedPublicSharedCatalog::class,
            [
                'sharedCatalogProductItemManagement' => $this->sharedCatalogProductItemManagementMock,
                'customerGroupManagement' => $this->customerGroupManagementMock,
                'catalogPermissionManagement' => $this->catalogPermissionManagementMock,
                'sharedCatalogCompanyManagement' => $this->sharedCatalogCompanyManagementMock,
                'categoryManagement' => $this->categoryManagementMock,
                'logger' => $this->loggerMock,
                'save' => $this->saveMock
            ]
        );
    }

    /**
     * Test for execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $publicCatalogId = 1;
        $sharedCatalogId = 1;
        $publicCatalogCategoryIds = [1];
        $sharedCatalogCategoryIds = [1];

        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $publicCatalog->expects($this->atLeastOnce())->method('setType')
            ->with(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM)->willReturnSelf();
        $this->sharedCatalogProductItemManagementMock->expects($this->once())->method('deletePricesForPublicCatalog');
        $this->customerGroupManagementMock->expects($this->once())->method('updateCustomerGroup')->with($sharedCatalog);
        $this->customerGroupManagementMock->expects($this->once())->method('setDefaultCustomerGroup')
            ->with($sharedCatalog);
        $this->sharedCatalogProductItemManagementMock->expects($this->once())->method('addPricesForPublicCatalog');
        $this->sharedCatalogCompanyManagementMock->expects($this->once())->method('unassignAllCompanies')
            ->with($publicCatalogId);
        $publicCatalog->expects($this->atLeastOnce())->method('getId')->willReturn($publicCatalogId);
        $this->catalogPermissionManagementMock->expects($this->once())->method('setDenyPermissions')
            ->with(
                $publicCatalogCategoryIds,
                [\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID]
            );
        $sharedCatalog->expects($this->atLeastOnce())->method('getId')->willReturn($sharedCatalogId);
        $this->categoryManagementMock->expects($this->exactly(2))->method('getCategories')
            ->withConsecutive([$publicCatalogId], [$sharedCatalogId])
            ->willReturnOnConsecutiveCalls($publicCatalogCategoryIds, $sharedCatalogCategoryIds);
        $this->catalogPermissionManagementMock->expects($this->once())->method('setAllowPermissions')
            ->with(
                $sharedCatalogCategoryIds,
                [\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID]
            );

        $this->assertInstanceOf(
            \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class,
            $this->duplicatedPublicSharedCatalog->execute($sharedCatalog, $publicCatalog)
        );
    }

    /**
     * Test for execute() method with LocalizedException.
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save shared catalog.
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exception = new \Magento\Framework\Exception\LocalizedException(__('exception message'));
        $publicCatalog = $this->prepareExecuteWithExceptions($exception);
        $this->loggerMock->expects($this->once())->method('critical')->with('exception message');

        $this->duplicatedPublicSharedCatalog->execute($sharedCatalog, $publicCatalog);
    }

    /**
     * Test for execute() method with CouldNotSaveException.
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage exception message
     * @return void
     */
    public function testExecuteWithCouldNotSaveException()
    {
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__('exception message'));
        $publicCatalog = $this->prepareExecuteWithExceptions($exception);

        $this->duplicatedPublicSharedCatalog->execute($sharedCatalog, $publicCatalog);
    }

    /**
     * Prepare mocks for execute() test with Exceptions.
     *
     * @param \Exception $exception
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareExecuteWithExceptions(\Exception $exception)
    {
        $publicCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $publicCatalog->expects($this->atLeastOnce())->method('setType')
            ->with(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM)->willReturnSelf();
        $this->sharedCatalogProductItemManagementMock->expects($this->once())->method('deletePricesForPublicCatalog');
        $this->saveMock->expects($this->once())->method('execute')->with($publicCatalog)
            ->willThrowException($exception);

        return $publicCatalog;
    }
}
