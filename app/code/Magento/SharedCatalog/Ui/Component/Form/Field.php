<?php
namespace Magento\SharedCatalog\Ui\Component\Form;

/**
 * Class Field
 */
class Field extends \Magento\Ui\Component\Form\Field
{
    /**
     * Field config key
     */
    const FIELD_CONFIG_KEY = 'config';

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->setData(
            self::FIELD_CONFIG_KEY,
            array_replace_recursive(
                (array) $this->getData(self::FIELD_CONFIG_KEY),
                (array) $this->getConfigDefaultData()
            )
        );
        parent::prepare();
    }

    /**
     * Get field config default data
     *
     * @return array
     */
    protected function getConfigDefaultData()
    {
        return [];
    }
}
