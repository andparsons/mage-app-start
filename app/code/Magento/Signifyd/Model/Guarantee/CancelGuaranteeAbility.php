<?php
namespace Magento\Signifyd\Model\Guarantee;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Model\CaseManagement;

/**
 * Checks if is possible to cancel Guarantee for order.
 */
class CancelGuaranteeAbility
{
    /**
     * @var CaseManagement
     */
    private $caseManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param CaseManagement $caseManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CaseManagement $caseManagement,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->caseManagement = $caseManagement;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Checks if it is possible to create Guarantee for order and case.
     *
     * @param int $orderId
     * @return bool
     */
    public function isAvailable($orderId)
    {
        $case = $this->caseManagement->getByOrderId($orderId);
        if ($case === null) {
            return false;
        }

        if (in_array($case->getGuaranteeDisposition(), [null, $case::GUARANTEE_CANCELED])) {
            return false;
        }

        $order = $this->getOrder($orderId);
        if (null === $order) {
            return false;
        }

        return true;
    }

    /**
     * Returns order by id
     *
     * @param int $orderId
     * @return OrderInterface|null
     */
    private function getOrder($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (InputException $e) {
            return null;
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $order;
    }
}
