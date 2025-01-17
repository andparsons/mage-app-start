<?php
namespace Magento\Checkout\Test\Unit\Model\Cart;

/**
 * Class RequestInfoFilterTest
 */
class RequestInfoFilterCompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Model\Cart\RequestInfoFilterComposite
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $requestInfoFilterMock1 = $this->createPartialMock(
            \Magento\Checkout\Model\Cart\RequestInfoFilter::class,
            ['filter']
        );
        $requestInfoFilterMock2 = $this->createPartialMock(
            \Magento\Checkout\Model\Cart\RequestInfoFilter::class,
            ['filter']
        );

        $requestInfoFilterMock1->expects($this->atLeastOnce())
            ->method('filter');
        $requestInfoFilterMock2->expects($this->atLeastOnce())
            ->method('filter');

        $filterList = [ $requestInfoFilterMock1, $requestInfoFilterMock2];

        $this->model = $this->objectManager->getObject(
            \Magento\Checkout\Model\Cart\RequestInfoFilterComposite::class,
            [
                'filters' => $filterList,
            ]
        );
    }

    /**
     * Test Filter method
     */
    public function testFilter()
    {
        /** @var \Magento\Framework\DataObject $params */
        $params = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            ['data' => ['abc' => 1, 'efg' => 1, 'xyz' => 1]]
        );
        $result = $this->model->filter($params);
        $this->assertEquals($this->model, $result);
    }
}
