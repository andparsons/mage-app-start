<?php
namespace Magento\QuickOrder\Test\Unit\Controller\Sku;

class UploadFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\QuickOrder\Controller\Index\Download
     */
    protected $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    public function testExecute()
    {
        $context = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $helper = $this->createMock(\Magento\AdvancedCheckout\Helper\Data::class);
        $helper->expects($this->any())
            ->method('isSkuFileUploaded')->will($this->returnValue(true));
        $helper->expects($this->any())
            ->method('processSkuFileUploading')->will($this->returnValue(['test', 'test 2']));
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $context->expects($this->any())
            ->method('getRequest')->will($this->returnValue($this->requestMock));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\QuickOrder\Controller\Sku\UploadFile::class,
            [
                'context' => $context,
                'advancedCheckoutHelper' => $helper
            ]
        );

        $this->controller->execute();
    }
}
