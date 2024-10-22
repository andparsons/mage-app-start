<?php
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Framework\App\Request\Http;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Tests cache debug headers and cache tag validation for a simple category query
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class CategoryCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
    }
    /**
     * Test cache tags and debug header for category and querying only for category
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testToCheckRequestCacheTagsForForCategory(): void
    {
        $categoryId ='333';
        $query
            = <<<QUERY
        {
            category(id: $categoryId) {
            id
            name
            url_key
            description
            product_count
           }
       }
QUERY;
        $request = $this->prepareRequest($query);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId,'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
