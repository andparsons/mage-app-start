<?php
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class ProductInMultipleStoresTest
 */
class ProductInMultipleStoresTest extends GraphQlAbstract
{
    /**
     * Test a product from a specific and a default store
     *
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductFromSpecificAndDefaultStore()
    {
        $productSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            attribute_set_id
            created_at
            id
            name
            price {
                minimalPrice {
                    amount {
                        value
                        currency
                    }
                }
            }
            sku
            type_id
            updated_at
            ... on PhysicalProductInterface {
                weight
            }
        }
    }
}
QUERY;

        /** @var \Magento\Store\Model\Store $store */
        $store =  ObjectManager::getInstance()->get(\Magento\Store\Model\Store::class);
        $storeCodeFromFixture = 'fixture_second_store';
        $storeId = $store->load($storeCodeFromFixture)->getStoreId();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku($productSku));

        //use case for custom store
        $productNameInFixtureStore = 'Product\'s Name in Fixture Store';
        $product->setName($productNameInFixtureStore)->setStoreId($storeId)->save();
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals(
            $productNameInFixtureStore,
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );

        //use case for default storeCode
        $nameInDefaultStore = 'Simple Product';
        $headerMapDefault = ['Store' => 'default'];
        $response = $this->graphQlQuery($query, [], '', $headerMapDefault);
        $this->assertEquals(
            $nameInDefaultStore,
            $response['products']['items'][0]['name'],
            'Product name in default store is invalid.'
        );

        //use case for empty storeCode
        $headerMapEmpty = ['Store' => ''];
        $response = $this->graphQlQuery($query, [], '', $headerMapEmpty);
        $this->assertEquals(
            $nameInDefaultStore,
            $response['products']['items'][0]['name'],
            'Product in the default store should be returned'
        );

        // use case for invalid storeCode
        $nonExistingStoreCode = "non_existent_store";
        $headerMapInvalidStoreCode = ['Store' => $nonExistingStoreCode];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Requested store is not found');
        $this->graphQlQuery($query, [], '', $headerMapInvalidStoreCode);
    }
}
