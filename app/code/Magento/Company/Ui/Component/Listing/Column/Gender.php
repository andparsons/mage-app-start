<?php
namespace Magento\Company\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Company\Model\Company\Source\Gender as GenderOptions;

class Gender extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var array
     */
    protected $genderOptions;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param GenderOptions $gender
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GenderOptions $gender,
        array $components = [],
        array $data = []
    ) {
        $this->genderOptions = $gender->toOptionArray();
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
                    $item[$fieldName] = $this->fieldLabel($item[$fieldName]);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $value
     * @return string
     */
    protected function fieldLabel($value)
    {
        $result = '';
        foreach ($this->genderOptions as $option) {
            if ($option['value'] == $value) {
                $result = $option['label'];
            }
        }
        return $result;
    }
}
