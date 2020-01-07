<?php
namespace Magento\RequisitionList\Block\Requisition\View;

use Magento\Backend\Block\Template\Context;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;

/**
 * Class Details
 *
 * @api
 * @since 100.0.0
 */
class Details extends \Magento\Framework\View\Element\Template
{
    /**
     * @var RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * Details constructor
     *
     * @param Context $context
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequisitionListRepositoryInterface $requisitionListRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->requisitionListRepository = $requisitionListRepository;
    }

    /**
     * Gets requisition list
     *
     * @return RequisitionListInterface|null
     */
    public function getRequisitionList()
    {
        $requisitionId = $this->getRequest()->getParam('requisition_id');
        if ($requisitionId === null) {
            return null;
        }
        return $this->requisitionListRepository->get($requisitionId);
    }

    /**
     * Gets item count
     *
     * @return int
     */
    public function getItemCount()
    {
        $list = $this->getRequisitionList();
        if ($list) {
            return count($list->getItems());
        }
        return 0;
    }

    /**
     * Get url for printing quote
     *
     * @return string
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print', [
            'requisition_id' => (int)$this->getRequest()->getParam('requisition_id')
        ]);
    }
}
