<?php

namespace Magento\Logging\Observer;

use Magento\Framework\Event\ObserverInterface;

class ModelDeleteAfterObserver implements ObserverInterface
{
    /**
     * Instance of \Magento\Logging\Model\Logging
     *
     * @var \Magento\Logging\Model\Processor
     */
    protected $_processor;

    /**
     * @param \Magento\Logging\Model\Processor $processor
     */
    public function __construct(
        \Magento\Logging\Model\Processor $processor
    ) {
        $this->_processor = $processor;
    }

    /**
     * Model after delete observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_processor->modelActionAfter($observer->getEvent()->getObject(), 'delete');
    }
}
