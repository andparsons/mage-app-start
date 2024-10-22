<?php

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class WizardTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Wizard
     */
    private $model;

    protected function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productBuilder = $this->getMockBuilder(\Magento\Catalog\Controller\Adminhtml\Product\Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->any())->method('getResultFactory')->willReturn($this->resultFactory);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Wizard::class,
            [
                'context' => $context,
                'productBuilder' => $this->productBuilder
            ]
        );
    }

    public function testExecute()
    {
        $this->productBuilder->expects($this->once())->method('build')->with($this->request);
        $this->resultFactory->expects($this->once())->method('create')->with(ResultFactory::TYPE_LAYOUT);

        $this->model->execute();
    }
}
