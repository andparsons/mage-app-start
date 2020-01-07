<?php

namespace Magento\QuickOrder\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\QuickOrder\Model\Config as ModuleConfig;

/**
 * Class AbstractAction
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ModuleConfig $moduleConfig
    ) {
        parent::__construct($context);
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->moduleConfig->isActive()) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }
}
