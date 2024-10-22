<?php
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);

$itemsToBuy = [
    'VIRT-1' => 5,
    'VIRT-2' => 6,
];

$searchCriteria = $searchCriteriaBuilder
    ->addFilter('reserved_order_id', 'created_order_for_test')
    ->create();
$cart = current($cartRepository->getList($searchCriteria)->getItems());

foreach ($itemsToBuy as $sku => $qty) {
    $product = $productRepository->get($sku);
    $requestData = [
        'product'           => $product->getProductId(),
        'qty'               => $qty
    ];
    $request = new \Magento\Framework\DataObject($requestData);
    $cart->addProduct($product, $request);
}

$cartRepository->save($cart);
$cartManagement->placeOrder($cart->getId());
