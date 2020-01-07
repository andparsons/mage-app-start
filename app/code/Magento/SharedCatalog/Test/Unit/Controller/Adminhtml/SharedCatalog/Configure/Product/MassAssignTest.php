<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\MassAssign;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Model\Form\Storage\SharedCatalogMassAssignment;

/**
 * Test for controller Adminhtml\SharedCatalog\Configure\Product\MassAssignTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassAssignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MassAssign
     */
    private $massAssign;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var SharedCatalogMassAssignment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogMassAssignment;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filter = $this->getMockBuilder(\Magento\Ui\Component\MassAction\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory = $this
            ->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->sharedCatalogMassAssignment = $this->getMockBuilder(SharedCatalogMassAssignment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->massAssign = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product\MassAssign::class,
            [
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'sharedCatalogMassAssignment' => $this->sharedCatalogMassAssignment,
                'filter' => $this->filter,
                'collectionFactory' => $this->collectionFactory,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'resultJsonFactory' => $this->resultJsonFactory,
                '_request' => $this->request
            ]
        );
    }

    /**
     * Unit test for execute().
     *
     * @return void
     */
    public function testExecute()
    {
        $configureKey = UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY;
        $isAssign = true;
        $sharedCatalogId = 256;
        $collection = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $filteredCollection = $this
            ->getMockBuilder(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filter->expects($this->atLeastOnce())->method('getCollection')->willReturn($filteredCollection);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->willReturnOnConsecutiveCalls($configureKey, $isAssign, $sharedCatalogId);
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory->expects($this->atLeastOnce())->method('create')->willReturn($storage);
        $this->sharedCatalogMassAssignment->expects($this->atLeastOnce())->method('assign')
            ->with($filteredCollection, $storage, $sharedCatalogId, (int)$isAssign);
        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultJson->expects($this->atLeastOnce())->method('setJsonData')->willReturnSelf();
        $this->resultJsonFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultJson);

        $this->assertEquals($resultJson, $this->massAssign->execute());
    }
}
