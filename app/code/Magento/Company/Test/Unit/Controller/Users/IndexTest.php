<?php

namespace Magento\Company\Test\Unit\Controller\Users;

/**
 * Class IndexTest.
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Company\Controller\Users\Index
     */
    private $index;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyContext = $this->createMock(
            \Magento\Company\Model\CompanyContext::class
        );
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->index = $objectManagerHelper->getObject(
            \Magento\Company\Controller\Users\Index::class,
            [
                'companyContext' => $this->companyContext,
                'resultFactory' => $this->resultFactory,
                'resultRedirectFactory' => $this->resultRedirectFactory,
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
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn(1);
        $result = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)->willReturn($result);
        $config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $result->expects($this->once())->method('getConfig')->willReturn($config);
        $title = $this->createMock(\Magento\Framework\View\Page\Title::class);
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $title->expects($this->once())->method('set')->with(__('Company Users'))->willReturnSelf();
        $this->assertEquals($result, $this->index->execute());
    }

    /**
     * Test for execute method with empty user id.
     *
     * @return void
     */
    public function testExecuteWithEmptyUserId()
    {
        $this->companyContext->expects($this->once())->method('getCustomerId')->willReturn(null);
        $result = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())->method('setRefererUrl')->willReturnSelf();
        $this->assertEquals($result, $this->index->execute());
    }
}
