<?php
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Api\Data\CaseInterfaceFactory;

require __DIR__ . '/order_with_customer_and_two_simple_products.php';

/** @var OrderManagementInterface $orderManagement */
$orderManagement = $objectManager->create(OrderManagementInterface::class);
$orderManagement->hold($order->getEntityId());

/** @var CaseInterfaceFactory $caseFactory */
$caseFactory = $objectManager->get(CaseInterfaceFactory::class);

$associatedTeam = [
    'teamName' => 'Some Team',
    'teamId' => 123,
    'getAutoDismiss' => true,
    'getTeamDismissalDays' => 3
];

/** @var CaseInterface $case */
$case = $caseFactory->create();
$case->setCaseId(123)
    ->setGuaranteeEligible(true)
    ->setStatus(CaseInterface::STATUS_PROCESSING)
    ->setScore(553)
    ->setOrderId($order->getEntityId())
    ->setAssociatedTeam($associatedTeam)
    ->setReviewDisposition(CaseInterface::DISPOSITION_GOOD)
    ->setGuaranteeDisposition(CaseInterface::GUARANTEE_PENDING)
    ->setCreatedAt('2016-12-12T15:17:17+0000')
    ->setUpdatedAt('2016-12-12T19:23:16+0000');

/** @var CaseRepositoryInterface $caseRepository */
$caseRepository = $objectManager->get(CaseRepositoryInterface::class);
$caseRepository->save($case);
