<?php
namespace Magento\AdminGws\Observer;

use Magento\Framework\Event\ObserverInterface;

class ValidateModelLoadAfter implements ObserverInterface
{
    /**
     * @var \Magento\AdminGws\Model\Role
     */
    protected $role;

    /**
     * @var \Magento\AdminGws\Model\CallbackInvoker
     */
    protected $callbackInvoker;

    /**
     * @var \Magento\AdminGws\Model\ConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\AdminGws\Model\Role $role
     * @param \Magento\AdminGws\Model\CallbackInvoker $callbackInvoker
     * @param \Magento\AdminGws\Model\ConfigInterface $config
     */
    public function __construct(
        \Magento\AdminGws\Model\Role $role,
        \Magento\AdminGws\Model\CallbackInvoker $callbackInvoker,
        \Magento\AdminGws\Model\ConfigInterface $config
    ) {
        $this->callbackInvoker = $callbackInvoker;
        $this->role = $role;
        $this->config = $config;
    }

    /**
     * Initialize a model after loading it
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->role->getIsAll()) {
            return;
        }

        $model = $observer->getEvent()->getObject();

        if (!($callback = $this->config->getCallbackForObject($model, 'model_load_after'))
        ) {
            return;
        }

        $this->callbackInvoker
            ->invoke(
                $callback,
                $this->config->getGroupProcessor('model_load_after'),
                $model
            );
    }
}
