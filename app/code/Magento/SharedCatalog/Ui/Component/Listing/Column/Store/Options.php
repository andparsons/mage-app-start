<?php
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Store;

/**
 * Store Options for Shared Catalog
 */
class Options extends \Magento\Store\Model\System\Store
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        /** @var \Magento\Store\Model\Group $group */
        foreach ($this->getGroupCollection() as $group) {
            $this->currentOptions[$group->getName()]['label'] = $group->getName();
            $this->currentOptions[$group->getName()]['value'] = $group->getId();
        }

        $this->options = array_values($this->currentOptions);
        return $this->options;
    }
}
