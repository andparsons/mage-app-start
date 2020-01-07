<?php
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\StateException;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassDelete extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * Shared Catalog Repository Interface
     * @var SharedCatalogRepositoryInterface
     */
    protected $sharedCatalogRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger,
        SharedCatalogRepositoryInterface $sharedCatalogRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory, $logger);
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Mass action
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $sharedCatalogsDeleted = 0;
        /** @var \Magento\SharedCatalog\Model\SharedCatalog $sharedCatalog */
        foreach ($collection as $sharedCatalog) {
            try {
                $this->sharedCatalogRepository->delete($sharedCatalog);
                $sharedCatalogsDeleted++;
            } catch (StateException $e) {
                $this->logger->critical($e);
                $this->messageManager->addError($e->getMessage());
            }
        }
        if ($sharedCatalogsDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $sharedCatalogsDeleted));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
