<?php
namespace Magento\Staging\Model\Entity\Update\Action;

/**
 * Interface \Magento\Staging\Model\Entity\Update\Action\ActionInterface
 *
 */
interface ActionInterface
{
    /**
     * Execute action
     *
     * @api
     * @param array $params
     * @return mixed
     */
    public function execute(array $params);
}
