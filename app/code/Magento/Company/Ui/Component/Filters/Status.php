<?php
namespace Magento\Company\Ui\Component\Filters;

/**
 * Class Status.
 */
class Status extends \Magento\Ui\Component\Filters
{
    /**
     * Prepare.
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        if (isset($config['params']) && is_array($config['params'])) {
            $config['params']['statusActive'] = \Magento\Company\Api\Data\CompanyCustomerInterface::STATUS_ACTIVE;
            $config['params']['statusInactive'] = \Magento\Company\Api\Data\CompanyCustomerInterface::STATUS_INACTIVE;
            $this->setData('config', $config);
        }
    }
}
