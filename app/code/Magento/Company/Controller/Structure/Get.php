<?php
namespace Magento\Company\Controller\Structure;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Get extends \Magento\Company\Controller\AbstractAction implements HttpGetActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::users_view';

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Get tree
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        /** @var \Magento\Company\Block\Company\Management $block */
        $block = $this->layoutFactory->create()->createBlock(\Magento\Company\Block\Company\Management::class);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'status' => 'ok',
                'message' => __('The tree retrieved successfully.'),
                'data' => $block->getTree()
            ]
        );
        return $resultJson;
    }
}
