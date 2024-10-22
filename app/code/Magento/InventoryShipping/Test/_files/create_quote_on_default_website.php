<?php
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
/** @var AddressInterfaceFactory $addressFactory */
$addressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);
/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);

$cartId = $cartManagement->createEmptyCart();
$cart = $cartRepository->get($cartId);
$cart->setCustomerEmail('admin@example.com');
$cart->setCustomerIsGuest(true);

/** @var AddressInterface $address */
$address = $addressFactory->create(
    [
        'data' => [
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_REGION_ID => 15,
            AddressInterface::KEY_LASTNAME => 'Doe',
            AddressInterface::KEY_FIRSTNAME => 'John',
            AddressInterface::KEY_STREET => 'example street',
            AddressInterface::KEY_EMAIL => 'customer@example.com',
            AddressInterface::KEY_CITY => 'example city',
            AddressInterface::KEY_TELEPHONE => '000 0000',
            AddressInterface::KEY_POSTCODE => 12345
        ]
    ]
);
$cart->setReservedOrderId('created_order_for_test');
$cart->setBillingAddress($address);
$cart->setShippingAddress($address);
$cart->getPayment()->setMethod('checkmo');
$cart->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$cart->getShippingAddress()->setCollectShippingRates(true);
$cart->getShippingAddress()->collectShippingRates();
$cartRepository->save($cart);
