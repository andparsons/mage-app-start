<?php
namespace Magento\Company\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Company\Model\Company;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->setStatusLabel($item[$fieldName]);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Set status label
     *
     * @param int $key
     * @return string
     */
    protected function setStatusLabel($key)
    {
        $labels = [
            Company::STATUS_PENDING => __('Pending Approval'),
            Company::STATUS_APPROVED => __('Active'),
            Company::STATUS_REJECTED => __('Rejected'),
            Company::STATUS_BLOCKED => __('Blocked')
        ];

        return $labels[$key];
    }
}
