<?php
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Tests cache debug headers and cache tag validation for a deep nested category and product query
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 */
class DeepNestedCategoriesAndProductsTest extends AbstractGraphqlCacheTest
{
    /** @var \Magento\GraphQl\Controller\GraphQl */
    private $graphql;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
    }

    /**
     * Test cache tags and debug header for deep nested queries involving category and products
     *
     * @magentoCache all enabled
     * @magentoDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     *
     */
    public function testDispatchForCacheHeadersOnDeepNestedQueries(): void
    {
        $baseCategoryId ='333';
        $query
            = <<<QUERY
        {
  category(id: $baseCategoryId) {
    products {
      items {
        attribute_set_id
        country_of_manufacture
        created_at
        description {
            html
        }
        gift_message_available
        id
        categories {
          name
          url_path
          available_sort_by
          level
          products {
            items {
              name
              id
            }
          }
        }
              }
    }
  }
}
QUERY;
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $resolvedCategoryIds = [];
        $category = $categoryRepository->get($baseCategoryId);

        $productIdsFromCategory = $category->getProductCollection()->getAllIds();
        foreach ($productIdsFromCategory as $productId) {
            $resolvedCategoryIds = array_merge(
                $resolvedCategoryIds,
                $productRepository->getById($productId)->getCategoryIds()
            );
        }

        $resolvedCategoryIds = array_merge($resolvedCategoryIds, [$baseCategoryId]);
        foreach ($resolvedCategoryIds as $categoryId) {
            $category = $categoryRepository->get($categoryId);
            $productIdsFromCategory= array_merge(
                $productIdsFromCategory,
                $category->getProductCollection()->getAllIds()
            );
        }

        $uniqueProductIds = array_unique($productIdsFromCategory);
        $uniqueCategoryIds = array_unique($resolvedCategoryIds);
        $expectedCacheTags = ['cat_c', 'cat_p', 'FPC'];
        foreach ($uniqueProductIds as $uniqueProductId) {
            $expectedCacheTags = array_merge($expectedCacheTags, ['cat_p_'.$uniqueProductId]);
        }
        foreach ($uniqueCategoryIds as $uniqueCategoryId) {
            $expectedCacheTags = array_merge($expectedCacheTags, ['cat_c_'.$uniqueCategoryId]);
        }

        $request = $this->prepareRequest($query);
        $response = $this->graphql->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEmpty(
            array_merge(
                array_diff($expectedCacheTags, $actualCacheTags),
                array_diff($actualCacheTags, $expectedCacheTags)
            )
        );
    }
}
