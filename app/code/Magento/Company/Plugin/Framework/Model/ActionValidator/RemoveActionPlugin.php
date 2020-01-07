<?php

namespace Magento\Company\Plugin\Framework\Model\ActionValidator;

use Magento\Framework\Model\AbstractModel;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Model\Company\Structure;

/**
 * Class RemoveActionPlugin.
 */
class RemoveActionPlugin
{
    /**
     * Customer model class name.
     *
     * @var string
     */
    private $customerModel = \Magento\Customer\Model\Customer::class;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var Structure
     */
    private $structureManager;

    /**
     * RemoveActionPlugin constructor.
     *
     * @param UserContextInterface $userContext
     * @param Structure $structureManager
     */
    public function __construct(UserContextInterface $userContext, Structure $structureManager)
    {
        $this->userContext = $userContext;
        $this->structureManager = $structureManager;
    }

    /**
     * Around isAllowed.
     *
     * @param \Magento\Framework\Model\ActionValidator\RemoveAction $subject
     * @param \Closure $proceed
     * @param AbstractModel $model
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAllowed(
        \Magento\Framework\Model\ActionValidator\RemoveAction $subject,
        \Closure $proceed,
        AbstractModel $model
    ) {
        if ($model instanceof $this->customerModel) {
            $customerId = $model->getId();
            $currentCustomerId = $this->userContext->getUserId();
            $allowedIds = $this->structureManager->getAllowedIds($currentCustomerId);

            if ($customerId && $currentCustomerId && $customerId != $currentCustomerId
                && in_array($customerId, $allowedIds['users'])) {
                return true;
            }
        }

        return $proceed($model);
    }
}
