<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\QuickOrder\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\QuickOrder\Model\Config as ModuleConfig;

class Index extends \Magento\QuickOrder\Controller\AbstractAction implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param ModuleConfig $moduleConfig
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ModuleConfig $moduleConfig,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $moduleConfig);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * View Quick Order page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Quick Order'));

        return $resultPage;
    }

    /**
     * Enable functionality not for only logged in customers but for not logged in also
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        /** @var $helper \Magento\AdvancedCheckout\Helper\Data */
        $helper = $this->_objectManager->get(\Magento\AdvancedCheckout\Helper\Data::class);
        if (!$helper->isSkuEnabled() || !$helper->isSkuApplied()) {
            return $this->_redirect('customer/account');
        }

        return parent::dispatch($request);
    }
}
