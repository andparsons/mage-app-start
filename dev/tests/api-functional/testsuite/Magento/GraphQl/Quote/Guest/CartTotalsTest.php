<?php
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting cart totals for guest
 */
class CartTotalsTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/apply_tax_for_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetCartTotalsWithTaxApplied()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('prices', $response['cart']);
        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(21.5, $pricesResponse['grand_total']['value']);
        self::assertEquals(21.5, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);

        $appliedTaxesResponse = $pricesResponse['applied_taxes'];

        self::assertEquals('US-TEST-*-Rate-1', $appliedTaxesResponse[0]['label']);
        self::assertEquals(1.5, $appliedTaxesResponse[0]['amount']['value']);
        self::assertEquals('USD', $appliedTaxesResponse[0]['amount']['currency']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/apply_tax_for_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetCartTotalsWithEmptyCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('prices', $response['cart']);
        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(0, $pricesResponse['grand_total']['value']);
        self::assertEquals(0, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(0, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(0, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);

        $appliedTaxesResponse = $pricesResponse['applied_taxes'];

        self::assertCount(0, $appliedTaxesResponse);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetTotalsWithNoTaxApplied()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(20, $pricesResponse['grand_total']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);
        self::assertEmpty($pricesResponse['applied_taxes']);
    }

    /**
     * The totals calculation is based on quote address.
     * But the totals should be calculated even if no address is set
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testGetCartTotalsWithNoAddressSet()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);

        $pricesResponse = $response['cart']['prices'];
        self::assertEquals(20, $pricesResponse['grand_total']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_including_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_excluding_tax']['value']);
        self::assertEquals(20, $pricesResponse['subtotal_with_discount_excluding_tax']['value']);
        self::assertEmpty($pricesResponse['applied_taxes']);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/apply_tax_for_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     */
    public function testGetSelectedShippingMethodFromCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery($query);
    }

    /**
     * Generates GraphQl query for retrieving cart totals
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    prices {
      grand_total {
        value,
        currency
      }
      subtotal_including_tax {
        value
        currency
      }
      subtotal_excluding_tax {
        value
        currency
      }
      subtotal_with_discount_excluding_tax {
        value
        currency
      }
      applied_taxes {
        label
        amount {
          value
          currency
        }
      }
    }
  }
}
QUERY;
    }
}
