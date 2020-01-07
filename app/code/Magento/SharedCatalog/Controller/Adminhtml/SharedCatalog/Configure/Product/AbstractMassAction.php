<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Product;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Abstract class for mass actions.
 */
abstract class AbstractMassAction extends \Magento\SharedCatalog\Controller\Adminhtml\AbstractJsonAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_SharedCatalog::manage';

    /**
     * Redirect url
     * @var string
     */
    protected $redirectUrl = '*/*/index';

    /**
     * Filters
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * Product collection factory
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Psr logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * AbstractMassAction constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $resultJsonFactory);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * Execute action.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->createJsonResponse(['data' => ['status' => false, 'error' => $e->getMessage()]]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->createJsonResponse(['data' => ['status' => false]]);
        }
    }

    /**
     * Execute action to collection items.
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     */
    abstract protected function massAction(AbstractCollection $collection);
}
