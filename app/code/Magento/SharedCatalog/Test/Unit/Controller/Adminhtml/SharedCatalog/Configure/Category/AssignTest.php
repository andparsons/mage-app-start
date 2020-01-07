<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure\Category;

/**
 * Assign category controller unit test.
 */
class AssignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogAssignment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogAssignment;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Category\Assign
     */
    private $controller;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->categoryRepository = $this->getMockBuilder(\Magento\Catalog\Api\CategoryRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogAssignment = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogAssignment::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Category\Assign::class,
            [
                '_request' => $this->request,
                'resultJsonFactory' => $this->resultJsonFactory,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'categoryRepository' => $this->categoryRepository,
                'sharedCatalogAssignment' => $this->sharedCatalogAssignment,
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
        $configureKey = 'configure_key_value';
        $categoryId = 1;
        $childrenCategoriesIds = [2, 3];
        $productsToAssign = [
            'skus' => ['SKU1', 'SKU2'],
            'category_ids' => [3, 4, 5],
        ];
        $isAssign = 1;
        $isGeneralAction = 0;
        $this->request->expects($this->exactly(4))->method('getParam')
            ->withConsecutive(['configure_key'], ['category_id'], ['is_assign'], ['is_include_subcategories'])
            ->willReturnOnConsecutiveCalls($configureKey, $categoryId, $isAssign, $isGeneralAction);
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')->with(['key' => $configureKey])->willReturn($storage);
        $category = $this->getMockBuilder(\Magento\Catalog\Api\Data\CategoryInterface::class)
            ->setMethods(['getAllChildren'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $category->expects($this->once())->method('getAllChildren')->with(true)->willReturn($childrenCategoriesIds);
        $this->sharedCatalogAssignment->expects($this->once())->method('getAssignProductsByCategoryIds')
            ->with(array_merge($childrenCategoriesIds, [$categoryId]))->willReturn($productsToAssign);
        $storage->expects($this->once())->method('assignProducts')->with($productsToAssign['skus']);
        $storage->expects($this->once())->method('assignCategories')
            ->with(array_unique(array_merge($childrenCategoriesIds, [$categoryId], $productsToAssign['category_ids'])));
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setJsonData')->with(
            json_encode(
                [
                    'data' => [
                        'status' => 1,
                        'category' => $categoryId,
                        'is_assign' => $isAssign
                    ]
                ]
            )
        )->willReturnSelf();
        $this->assertEquals($result, $this->controller->execute());
    }

    /**
     * Test for execute method with unassign action.
     *
     * @return void
     */
    public function testExecuteUnassignAction()
    {
        $configureKey = 'configure_key_value';
        $categoryId = 2;
        $assignedCategoriesIds = [1, 2, 3];
        $productSkus = ['SKU1', 'SKU2'];
        $isAssign = 0;
        $isGeneralAction = 0;
        $this->request->expects($this->exactly(4))->method('getParam')
            ->withConsecutive(['configure_key'], ['category_id'], ['is_assign'])
            ->willReturnOnConsecutiveCalls($configureKey, $categoryId, $isAssign, $isGeneralAction);
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()->getMock();
        $this->wizardStorageFactory->expects($this->once())
            ->method('create')->with(['key' => $configureKey])->willReturn($storage);
        $storage->expects($this->once())->method('getAssignedCategoriesIds')->willReturn($assignedCategoriesIds);
        $this->sharedCatalogAssignment->expects($this->once())->method('getProductSkusToUnassign')
            ->with([$categoryId], array_diff($assignedCategoriesIds, [$categoryId]))->willReturn($productSkus);
        $storage->expects($this->once())->method('unassignProducts')->with($productSkus);
        $storage->expects($this->once())->method('unassignCategories')->with([$categoryId]);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setJsonData')->with(
            json_encode(
                [
                    'data' => [
                        'status' => 1,
                        'category' => $categoryId,
                        'is_assign' => $isAssign
                    ]
                ]
            )
        )->willReturnSelf();
        $this->assertEquals($result, $this->controller->execute());
    }
}
