<?php

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Controller\Adminhtml\Category\Delete */
    protected $unit;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryRepository;

    /** @var \Magento\Backend\Model\Auth\StorageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authStorage;

    protected function setUp()
    {
        $context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $resultRedirectFactory = $this->createPartialMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create']
        );
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam', 'getPost']
        );
        $auth = $this->createPartialMock(\Magento\Backend\Model\Auth::class, ['getAuthStorage']);
        $this->authStorage = $this->createPartialMock(
            \Magento\Backend\Model\Auth\StorageInterface::class,
            ['processLogin', 'processLogout', 'isLoggedIn', 'prolong', 'setDeletedPath']
        );
        $eventManager = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $response = $this->getMockForAbstractClass(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            '',
            false
        );
        $messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['addSuccessMessage']
        );
        $this->categoryRepository = $this->createMock(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $context->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $context->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));
        $context->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $context->expects($this->any())
            ->method('getAuth')
            ->will($this->returnValue($auth));
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);
        $auth->expects($this->any())
            ->method('getAuthStorage')
            ->will($this->returnValue($this->authStorage));

        $this->resultRedirect = $this->createMock(\Magento\Backend\Model\View\Result\Redirect::class);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->unit = (new ObjectManagerHelper($this))->getObject(
            \Magento\Catalog\Controller\Adminhtml\Category\Delete::class,
            [
                'context' => $context,
                'categoryRepository' => $this->categoryRepository
            ]
        );
    }

    public function testDeleteWithoutCategoryId()
    {
        $this->request->expects($this->any())->method('getParam')->with('id')->willReturn(null);
        $this->resultRedirect->expects($this->once())->method('setPath')
            ->with('catalog/*/', ['_current' => true, 'id' => null]);
        $this->categoryRepository->expects($this->never())->method('get');

        $this->unit->execute();
    }

    public function testDelete()
    {
        $categoryId = 5;
        $parentId = 7;
        $this->request->expects($this->any())->method('getParam')->with('id')->willReturn($categoryId);
        $category = $this->createPartialMock(\Magento\Catalog\Model\Category::class, ['getParentId', 'getPath']);
        $category->expects($this->once())->method('getParentId')->willReturn($parentId);
        $category->expects($this->once())->method('getPath')->willReturn('category-path');
        $this->categoryRepository->expects($this->once())->method('get')->with($categoryId)->willReturn($category);
        $this->authStorage->expects($this->once())->method('setDeletedPath')->with('category-path');
        $this->resultRedirect->expects($this->once())->method('setPath')
            ->with('catalog/*/', ['_current' => true, 'id' => $parentId]);

        $this->unit->execute();
    }
}
