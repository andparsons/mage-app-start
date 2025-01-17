<?php
namespace Magento\Catalog\Controller\Adminhtml\Product\Action;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;

/**
 * @magentoAppArea adminhtml
 */
class AttributeTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /** @var PublisherConsumerController */
    private $publisherConsumerController;
    private $consumers = ['product_action_attribute.update'];

    protected function setUp()
    {
        $this->publisherConsumerController = Bootstrap::getObjectManager()->create(PublisherConsumerController::class, [
            'consumers' => $this->consumers,
            'logFilePath' => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
            'maxMessages' => null,
            'appInitParams' => Bootstrap::getInstance()->getAppInitParams()
        ]);

        try {
            $this->publisherConsumerController->startConsumers();
        } catch (\Magento\TestFramework\MessageQueue\EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (\Magento\TestFramework\MessageQueue\PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionRedirectsSuccessfully()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get(\Magento\Backend\Model\Session::class);
        $session->setProductIds([1]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        /** @var \Magento\Backend\Model\UrlInterface $urlBuilder */
        $urlBuilder = $objectManager->get(\Magento\Framework\UrlInterface::class);

        /** @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper */
        $attributeHelper = $objectManager->get(\Magento\Catalog\Helper\Product\Edit\Action\Attribute::class);
        $expectedUrl = $urlBuilder->getUrl(
            'catalog/product/index',
            ['store' => $attributeHelper->getSelectedStoreId()]
        );
        $isRedirectPresent = false;
        foreach ($this->getResponse()->getHeaders() as $header) {
            if ($header->getFieldName() === 'Location' && strpos($header->getFieldValue(), $expectedUrl) === 0) {
                $isRedirectPresent = true;
            }
        }

        $this->assertTrue($isRedirectPresent);
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     *
     * @dataProvider saveActionVisibilityAttrDataProvider
     * @param array $attributes
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionChangeVisibility($attributes)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepository $repository */
        $repository = Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $product = $repository->get('simple');
        $product->setOrigData();
        $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
        $product->save();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get(\Magento\Backend\Model\Session::class);
        $session->setProductIds([$product->getId()]);
        $this->getRequest()->setParam('attributes', $attributes);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        /** @var \Magento\Catalog\Model\Category $category */
        $categoryFactory = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        /** @var \Magento\Catalog\Block\Product\ListProduct $listProduct */
        $listProduct = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Block\Product\ListProduct::class
        );

        $this->publisherConsumerController->waitForAsynchronousResult(
            function () use ($repository) {
                sleep(3);
                return $repository->get(
                    'simple',
                    false,
                    null,
                    true
                )->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE;
            },
            []
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productItem = $productCollection->getFirstItem();
        $this->assertEquals($session->getProductIds(), [$productItem->getId()]);
    }

    /**
     * @param array $attributes Request parameter.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Validate::execute
     *
     * @dataProvider validateActionDataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDbIsolation disabled
     */
    public function testValidateActionWithMassUpdate($attributes)
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get(\Magento\Backend\Model\Session::class);
        $session->setProductIds([1, 2]);

        $this->getRequest()->setParam('attributes', $attributes);

        $this->dispatch('backend/catalog/product_action_attribute/validate/store/0');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $response = $this->getResponse()->getBody();
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('error', $data);
        $this->assertFalse($data['error']);
        $this->assertCount(1, $data);
    }

    /**
     * Data Provider for validation
     *
     * @return array
     */
    public function validateActionDataProvider()
    {
        return [
            [
                'arguments' => [
                    'name'              => 'Name',
                    'description'       => 'Description',
                    'short_description' => 'Short Description',
                    'price'             => '512',
                    'weight'            => '16',
                    'meta_title'        => 'Meta Title',
                    'meta_keyword'      => 'Meta Keywords',
                    'meta_description'  => 'Meta Description',
                ],
            ]
        ];
    }

    /**
     * Data Provider for save with visibility attribute
     *
     * @return array
     */
    public function saveActionVisibilityAttrDataProvider()
    {
        return [
            ['arguments' => ['visibility' => Visibility::VISIBILITY_BOTH]],
            ['arguments' => ['visibility' => Visibility::VISIBILITY_IN_CATALOG]]
        ];
    }
}
