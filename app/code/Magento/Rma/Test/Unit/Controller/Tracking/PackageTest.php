<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rma\Test\Unit\Controller\Tracking;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Rma\Controller\Tracking\Package;
use Magento\Rma\Model\Shipping;
use Magento\Rma\Model\Shipping\Info;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class to test controller to load Rma Packages
 */
class PackageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ViewInterface|MockObject
     */
    private $viewMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var Info|MockObject
     */
    private $shippingInfoMock;

    /**
     * @var Package
     */
    private $controller;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->viewMock = $this->createMock(ViewInterface::class);
        $this->contextMock->method('getView')->willReturn($this->viewMock);
        $this->registryMock = $this->createMock(Registry::class);
        $this->shippingInfoMock = $this->createMock(Info::class);

        $objectManager = new ObjectManager($this);
        $this->controller = $objectManager->getObject(
            Package::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->registryMock,
                'shippingInfo' => $this->shippingInfoMock,
            ]
        );
    }

    /**
     * Test to execute tracking package
     */
    public function testExecute()
    {
        $hash = 'hash123';
        $packagesVal = 'packages vale';

        $this->requestMock->method('getParam')
            ->with('hash')
            ->willReturn($hash);
        $this->viewMock->expects($this->once())->method('loadLayout');
        $this->viewMock->expects($this->once())->method('renderLayout');

        $shippingLabelMock = $this->createPartialMock(Shipping::class, ['getPackages']);
        $shippingLabelMock->method('getPackages')
            ->willReturn($packagesVal);
        $this->shippingInfoMock->expects($this->once())
            ->method('loadPackage')
            ->with($hash)
            ->willReturn($shippingLabelMock);

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('rma_package_shipping', $shippingLabelMock);

        $this->controller->execute();
    }

    /**
     * Test to execute tracking package with NotFoundException
     *
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Page not found.
     */
    public function testExecuteNotFoundException()
    {
        $hash = 'hash123';
        $this->requestMock->method('getParam')
            ->with('hash')
            ->willReturn($hash);
        $shippingLabelMock = $this->createPartialMock(Shipping::class, ['getPackages']);
        $shippingLabelMock->method('getPackages')->willReturn('');
        $this->shippingInfoMock->expects($this->once())
            ->method('loadPackage')
            ->with($hash)
            ->willReturn($shippingLabelMock);

        $this->viewMock->expects($this->never())->method('loadLayout');
        $this->viewMock->expects($this->never())->method('renderLayout');
        $this->registryMock->expects($this->never())
            ->method('register')
            ->with('rma_package_shipping', $shippingLabelMock);

        $this->controller->execute();
    }
}
