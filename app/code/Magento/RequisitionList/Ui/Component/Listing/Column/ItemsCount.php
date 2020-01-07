<?php
namespace Magento\RequisitionList\Ui\Component\Listing\Column;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;

/**
 * Class Items
 */
class ItemsCount extends Column
{
    /**
     * @var RequisitionListRepositoryInterface
     */
    protected $requisitionListRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RequisitionListRepositoryInterface $requisitionListRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->requisitionListRepository = $requisitionListRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                try {
                    $listItems = $this->requisitionListRepository->get($item['entity_id'])->getItems();
                } catch (NoSuchEntityException $e) {
                    $listItems = [];
                }
                $item['items'] = count($listItems);
            }
        }

        return $dataSource;
    }
}
