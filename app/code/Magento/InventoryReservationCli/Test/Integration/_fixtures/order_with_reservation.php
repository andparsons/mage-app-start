<?php
declare(strict_types=1);

use Magento\Sales\Model\Order\ShipmentFactory;

require __DIR__ . '/../../../../../../../dev/tests/integration/testsuite/Magento/Sales/_files/order.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class)
    ->loadByIncrementId('100000001');

/** @var \Magento\Sales\Api\OrderManagementInterface $orderManagement */
$orderManagement = $objectManager->create(\Magento\Sales\Api\OrderManagementInterface::class);

$orderManagement->place($order);

/** @var \Magento\Sales\Model\Service\InvoiceService $invoiceService */
$invoiceService = $objectManager->create(\Magento\Sales\Api\InvoiceManagementInterface::class);

/** @var \Magento\Framework\DB\Transaction $transaction */
$transaction = $objectManager->create(\Magento\Framework\DB\Transaction::class);

$order->setData(
    'base_to_global_rate',
    1
)->setData(
    'base_to_order_rate',
    1
)->setData(
    'shipping_amount',
    20
)->setData(
    'base_shipping_amount',
    20
);

$invoice = $invoiceService->prepareInvoice($order);
$invoice->register();

$order->setIsInProcess(true);

$items = [];
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
}
$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
$shipment->register();

$transaction->addObject($invoice)->addObject($shipment)->addObject($order)->save();
