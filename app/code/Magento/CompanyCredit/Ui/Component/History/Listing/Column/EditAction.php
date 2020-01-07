<?php

namespace Magento\CompanyCredit\Ui\Component\History\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\CompanyCredit\Model\HistoryInterface;

/**
 * Class EditAction.
 */
class EditAction extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * EditAction constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            $creditModal = 'company_form.company_form.modalContainer.company_credit_form_modal';
            $amountField = $creditModal . '.reimburse_balance.amount';
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['type'] == HistoryInterface::TYPE_REIMBURSED) {
                    $item['credit_comment'] = $item['comment'];
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'credit/*/edit',
                            ['id' => $item['entity_id'], 'store' => $storeId]
                        ),
                        'label' => __('Edit'),
                        'hidden' => false,
                        'callback' => [
                            [
                                'provider' => $creditModal,
                                'target' => 'openModal',
                                'params' => [
                                    'url' => $this->urlBuilder->getUrl(
                                        'credit/*/edit',
                                        ['id' => $item['entity_id'],
                                            'store' => $storeId
                                        ]
                                    ),
                                        'item' => $item,
                                    ],
                            ],
                            [
                                'provider' => $amountField,
                                'target' => 'disable'
                            ]
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
