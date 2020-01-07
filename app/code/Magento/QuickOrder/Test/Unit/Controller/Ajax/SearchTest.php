<?php
namespace Magento\QuickOrder\Test\Unit\Controller\Ajax;

class SearchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\QuickOrder\Controller\Ajax\Search
     */
    protected $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $resultJson;

    protected function setUp()
    {
        $context = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->requestMock = $this->createMock(\Magento\Framework\HTTP\PhpEnvironment\Request::class);
        $context->expects($this->any())
            ->method('getRequest')->will($this->returnValue($this->requestMock));

        $resultJsonFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\JsonFactory::class, ['create']);

        $this->resultJson = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $resultJsonFactory->expects($this->any())
            ->method('create')->will($this->returnValue($this->resultJson));

        $cart = $this->createMock(\Magento\QuickOrder\Model\Cart::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\QuickOrder\Controller\Ajax\Search::class,
            [
                'context' => $context,
                'resultJsonFactory' => $resultJsonFactory,
                'cart' => $cart
            ]
        );
    }

    /**
     * @param array $items
     * @param string $status
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($items, $status)
    {
        $this->requestMock->expects($this->any())
            ->method('getPostValue')->will($this->returnValue(
                [
                    'items' => json_encode($items)
                ]
            ));

        $error = '';
        $setDataCallback = function ($data) use (&$error) {
            $error = $data['generalErrorMessage'];
        };
        $this->resultJson->expects($this->any())
            ->method('setData')->will($this->returnCallback($setDataCallback));
        $this->controller->execute();
        $this->assertEquals($status, $error);
    }

    public function executeDataProvider()
    {
        return [
            [
                [
                    [
                        'sku' => 'aaa'
                    ]
                ],
                ''
            ],
            [
                [
                    [
                        'qty' => 1
                    ]
                ],
                'The uploaded CSV file does not contain a column labelled SKU. Make sure the first column is '
                . 'labelled SKU and that each line in the file contains a SKU value. Then upload the file again.'
            ]
        ];
    }
}
