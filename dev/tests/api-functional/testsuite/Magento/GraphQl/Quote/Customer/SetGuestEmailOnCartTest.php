<?php
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setGuestEmailOnCart mutation
 */
class SetGuestEmailOnCartTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The request is not allowed for logged in customers
     */
    public function testSetGuestEmailOnCartForLoggedInCustomer()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The request is not allowed for logged in customers
     */
    public function testSetGuestEmailOnGuestCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Returns GraphQl mutation query for setting email address for a guest
     *
     * @param string $maskedQuoteId
     * @param string $email
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $email): string
    {
        return <<<QUERY
mutation {
  setGuestEmailOnCart(input: {
    cart_id: "$maskedQuoteId"
    email: "$email"
  }) {
    cart {
      email
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
