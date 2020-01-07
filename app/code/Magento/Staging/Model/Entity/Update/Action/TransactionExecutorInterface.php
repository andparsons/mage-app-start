<?php
namespace Magento\Staging\Model\Entity\Update\Action;

/**
 * Interface \Magento\Staging\Model\Entity\Update\Action\TransactionExecutorInterface
 *
 */
interface TransactionExecutorInterface extends ActionInterface
{
    /**
     * @param ActionInterface $action
     * @return mixed
     */
    public function setAction(ActionInterface $action);
}
