<?php
namespace Magento\NegotiableQuote\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\NegotiableQuote\Model\Status\LabelProviderInterface;

/**
 * Class Status
 */
class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var LabelProviderInterface
     */
    protected $labelProvider;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LabelProviderInterface $labelProvider
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LabelProviderInterface $labelProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->labelProvider = $labelProvider;
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
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName . '_original'] = $item[$fieldName];
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
        $labels = $this->labelProvider->getStatusLabels();

        return $labels[$key];
    }
}
