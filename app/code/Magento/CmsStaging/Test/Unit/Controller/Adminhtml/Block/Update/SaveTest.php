<?php

namespace Magento\CmsStaging\Test\Unit\Controller\Adminhtml\Block\Update;

use Magento\CmsStaging\Controller\Adminhtml\Block\Update\Save as SaveController;

class SaveTest extends \PHPUnit\Framework\TestCase
{
    /** @var SaveController */
    protected $controller;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Staging\Model\Entity\Update\Save|\PHPUnit_Framework_MockObject_MockObject */
    protected $stagingUpdateSave;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods([
                'getPostValue',
                'getParam',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'setParams',
                'getParams',
                'getCookie',
                'isSecure',
            ])
            ->getMock();
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->stagingUpdateSave = $this->getMockBuilder(\Magento\Staging\Model\Entity\Update\Save::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new SaveController($this->context, $this->stagingUpdateSave);
    }

    public function testExecute()
    {
        $blockId = 1;
        $entityData = [];
        $staging = [];
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(
                ['block_id'],
                ['staging']
            )
            ->willReturnOnConsecutiveCalls(
                $blockId,
                $staging
            );
        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn($entityData);
        $this->stagingUpdateSave
            ->expects($this->once())
            ->method('execute')
            ->with([
                'entityId' => $blockId,
                'stagingData' => $staging,
                'entityData' => $entityData
            ])
            ->willReturn(true);
        $this->assertTrue($this->controller->execute());
    }
}
