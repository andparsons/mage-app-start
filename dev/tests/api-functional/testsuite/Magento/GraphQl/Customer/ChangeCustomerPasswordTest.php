<?php
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test change customer password
 */
class ChangeCustomerPasswordTest extends GraphQlAbstract
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp()
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->accountManagement = Bootstrap::getObjectManager()->get(AccountManagementInterface::class);
        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $this->customerAuthUpdate = Bootstrap::getObjectManager()->get(CustomerAuthUpdate::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePassword()
    {
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $newPassword = 'anotherPassword1';

        $query = $this->getQuery($currentPassword, $newPassword);
        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);

        $response = $this->graphQlMutation($query, [], '', $headerMap);
        $this->assertEquals($customerEmail, $response['changeCustomerPassword']['email']);

        try {
            // registry contains the old password hash so needs to be reset
            $this->customerRegistry->removeByEmail($customerEmail);
            $this->accountManagement->authenticate($customerEmail, $newPassword);
        } catch (LocalizedException $e) {
            $this->fail('Password was not changed: ' . $e->getMessage());
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testChangePasswordIfUserIsNotAuthorizedTest()
    {
        $query = $this->getQuery('currentpassword', 'newpassword');
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangeWeakPassword()
    {
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $newPassword = 'weakpass';

        $query = $this->getQuery($currentPassword, $newPassword);
        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/Minimum of different classes of characters in password is.*/');

        $this->graphQlMutation($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid login or password.
     */
    public function testChangePasswordIfPasswordIsInvalid()
    {
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $newPassword = 'anotherPassword1';
        $incorrectCurrentPassword = 'password-incorrect';

        $query = $this->getQuery($incorrectCurrentPassword, $newPassword);

        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);
        $this->graphQlMutation($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Specify the "currentPassword" value.
     */
    public function testChangePasswordIfCurrentPasswordIsEmpty()
    {
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $newPassword = 'anotherPassword1';
        $incorrectCurrentPassword = '';

        $query = $this->getQuery($incorrectCurrentPassword, $newPassword);

        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);
        $this->graphQlMutation($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Specify the "newPassword" value.
     */
    public function testChangePasswordIfNewPasswordIsEmpty()
    {
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $incorrectNewPassword = '';

        $query = $this->getQuery($currentPassword, $incorrectNewPassword);

        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);
        $this->graphQlMutation($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage The account is locked.
     */
    public function testChangePasswordIfCustomerIsLocked()
    {
        $customerEmail = 'customer@example.com';
        $currentPassword = 'password';
        $newPassword = 'anotherPassword1';

        $this->lockCustomer(1);
        $query = $this->getQuery($currentPassword, $newPassword);

        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $currentPassword);
        $this->graphQlMutation($query, [], '', $headerMap);
    }

    /**
     * @param int $customerId
     *
     * @return void
     * @throws NoSuchEntityException
     */
    private function lockCustomer(int $customerId): void
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $customerSecure->setLockExpires('2030-12-31 00:00:00');
        $this->customerAuthUpdate->saveAuth($customerId);
    }

    /**
     * @param $currentPassword
     * @param $newPassword
     *
     * @return string
     */
    private function getQuery($currentPassword, $newPassword)
    {
        $query = <<<QUERY
mutation {
  changeCustomerPassword(
    currentPassword: "$currentPassword",
    newPassword: "$newPassword"
  ) {
    id
    email
    firstname
    lastname
  }
}
QUERY;

        return $query;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
