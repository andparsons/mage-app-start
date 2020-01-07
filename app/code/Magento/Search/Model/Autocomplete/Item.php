<?php

namespace Magento\Search\Model\Autocomplete;

class Item extends \Magento\Framework\DataObject implements ItemInterface
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getTitle()
    {
        return $this->_getData('title');
    }
}
