<?php
namespace Magento\Support\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Support\Model\Backup\Status;

class CodeDump extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Support\Model\BackupFactory
     */
    protected $backupFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Support\Model\Backup\Status $status
     * @param \Magento\Support\Model\BackupFactory $backupFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Support\Model\Backup\Status $status,
        \Magento\Support\Model\BackupFactory $backupFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->status = $status;
        $this->backupFactory = $backupFactory;
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
                    /** @var \Magento\Support\Model\Backup $backup */
                    $backup = $this->backupFactory->create();
                    foreach ($item as $field => $value) {
                        $backup->setData(strtolower($field), $value);
                    }
                    $items = $backup->getItems();
                    $item[$fieldName] = [
                        'label' => $this->status->getCodeDumpLabel($items['code']),
                        'value' => $this->status->getValue($items['code']),
                        'size' => $this->status->getSize($items['code'])
                    ];
                }
            }
        }

        return $dataSource;
    }
}
