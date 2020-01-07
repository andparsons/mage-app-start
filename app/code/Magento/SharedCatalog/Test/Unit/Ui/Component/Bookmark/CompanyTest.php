<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\Component\Bookmark;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Test for UI  Component\Bookmark\Company.
 */
class CompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Bookmark\Company
     */
    private $company;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bookmarkRepository;

    /**
     * @var \Magento\Ui\Api\BookmarkManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bookmarkManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\CompanyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogManagement;

    /**
     * @var \Magento\Ui\Api\BookmarkManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->setMethods(['register', 'notify'])
            ->disableOriginalConstructor()->getMock();
        $processor->expects($this->exactly(1))->method('register');
        $processor->expects($this->exactly(1))->method('notify');

        $this->context = $this
            ->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->setMethods(['getRequestParam', 'getProcessor', 'getNamespace', 'addComponentDefinition'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $configureKey = 'sadg346347sdf345';
        $mapForGetRequestParamMethod = [
            [UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY, null, $configureKey]
        ];
        $this->context->expects($this->exactly(2))->method('getRequestParam')
            ->willReturnMap($mapForGetRequestParamMethod);
        $this->context->expects($this->exactly(2))->method('getProcessor')->willReturn($processor);
        $namespace = '';
        $this->context->expects($this->exactly(2))->method('getNamespace')->willReturn($namespace);
        $this->context->expects($this->exactly(2))->method('addComponentDefinition');

        $this->bookmarkRepository = $this
            ->getMockBuilder(\Magento\Ui\Api\BookmarkRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->bookmarkManagement = $this
            ->getMockBuilder(\Magento\Ui\Api\BookmarkManagementInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->storage = $this->getMockBuilder(\Magento\Ui\Api\BookmarkManagementInterface::class)
            ->setMethods(['getAssignedCompaniesIds'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->companyStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\CompanyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->companyStorageFactory->expects($this->exactly(1))->method('create')->willReturn($this->storage);

        $this->catalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->setMethods(['getPublicCatalog'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->company = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Ui\Component\Bookmark\Company::class,
            [
                'context' => $this->context,
                'bookmarkRepository' => $this->bookmarkRepository,
                'bookmarkManagement' => $this->bookmarkManagement,
                'companyStorageFactory' => $this->companyStorageFactory,
                'catalogManagement' => $this->catalogManagement
            ]
        );
    }

    /**
     * Test for prepare().
     *
     * @param array $assignedCompaniesIds
     * @param array $calls
     * @dataProvider prepareDataProvider
     * @return void
     */
    public function testPrepare(array $assignedCompaniesIds, array $calls)
    {
        $this->storage->expects($this->exactly(1))->method('getAssignedCompaniesIds')
            ->willReturn($assignedCompaniesIds);

        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $sharedCatalogId = 235;
        $sharedCatalog->expects($this->exactly($calls['sharedCatalog_getId']))->method('getId')
            ->willReturn($sharedCatalogId);

        $this->catalogManagement->expects($this->exactly($calls['catalogManagement_getPublicCatalog']))
            ->method('getPublicCatalog')->willReturn($sharedCatalog);

        $this->company->prepare();
    }

    /**
     * Data provider for prepare() test.
     *
     * @return array
     */
    public function prepareDataProvider()
    {
        $companyId = 23;
        return [
            [
                [$companyId], ['catalogManagement_getPublicCatalog' => 0, 'sharedCatalog_getId' => 0]
            ],
            [
                [], ['catalogManagement_getPublicCatalog' => 1, 'sharedCatalog_getId' => 1]
            ]
        ];
    }
}
