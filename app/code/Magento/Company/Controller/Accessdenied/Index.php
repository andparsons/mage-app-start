<?php
namespace Magento\Company\Controller\Accessdenied;

/**
 * Storefront permissions access denied controller.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Cms\Helper\Page
     */
    private $pageHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var \Magento\Company\Api\StatusServiceInterface
     */
    private $moduleConfig;

    /**
     * @var string
     */
    private $accessDeniedPageId = 'access-denied-page';

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Cms\Helper\Page $pageHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Company\Api\StatusServiceInterface $moduleConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Cms\Helper\Page $pageHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Company\Api\StatusServiceInterface $moduleConfig
    ) {
        parent::__construct($context);
        $this->pageHelper = $pageHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Access denied page for company user.
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->moduleConfig->isActive()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Page not found.'));
        }

        $page = $this->pageHelper->prepareResultPage($this, $this->accessDeniedPageId);

        if ($page) {
            $page->setStatusHeader(403, '1.1', 'Forbidden');
            return $page;
        }

        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->setController('index');
        $resultForward->forward('defaultNoRoute');
        return $resultForward;
    }
}
