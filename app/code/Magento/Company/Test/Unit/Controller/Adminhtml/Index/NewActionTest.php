<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

/**
 * Class NewActionTest.
 */
class NewActionTest extends \PHPUnit\Framework\TestCase
{
    protected $new;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $title;

    /**
     * Set up
     */
    protected function setUp()
    {
        $resultForwardFactory = $this->createMock(\Magento\Backend\Model\View\Result\ForwardFactory::class);
        $pageFactory = $this->createPartialMock(
            \Magento\Framework\View\Result\PageFactory::class,
            ['create']
        );
        $page = $this->createPartialMock(
            \Magento\Framework\View\Result\Page::class,
            ['setActiveMenu', 'getConfig']
        );
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config->expects($this->atLeastOnce())->method('getTitle')->willReturn($this->title);
        $page->expects($this->atLeastOnce())->method('getConfig')->willReturn($config);
        $pageFactory->expects($this->atLeastOnce())->method('create')->willReturn($page);

        $companyRepository = $this->createMock(\Magento\Company\Api\CompanyRepositoryInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $coreRegistry = $this->createMock(\Magento\Framework\Registry::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->new = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Adminhtml\Index\NewAction::class,
            [
                'coreRegistry' => $coreRegistry,
                'resultForwardFactory' => $resultForwardFactory,
                'resultPageFactory' => $pageFactory,
                'companyRepository' => $companyRepository,
                '_request' => $this->request
            ]
        );
    }

    /**
     * @dataProvider dataForExecute
     *
     * @param $companyId
     * @param $title
     */
    public function testExecute($companyId, $title)
    {
        $this->request->expects($this->any())->method('getParam')->willReturn($companyId);
        $result = '';
        $prependCallback = function ($prefix) use (&$result) {
            $result = $prefix;
        };
        $this->title->expects($this->once())->method('prepend')->will($this->returnCallback($prependCallback));

        $this->new->execute();
        $this->assertEquals($title, $result);
    }

    public function dataForExecute()
    {
        return [
            [0, 'New Company']
        ];
    }
}
