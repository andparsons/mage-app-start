<?php
namespace Magento\Store\Ui\Component\Listing\Column\Store;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(SystemStore $systemStore, Escaper $escaper)
    {
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
    }

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

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }

    /**
     * Sanitize website/store option name
     *
     * @param string $name
     *
     * @return string
     */
    protected function sanitizeName($name)
    {
        $matches = [];
        preg_match('/\$[:]*{(.)*}/', $name, $matches);
        if (count($matches) > 0) {
            $name = $this->escaper->escapeHtml($this->escaper->escapeJs($name));
        } else {
            $name = $this->escaper->escapeHtml($name);
        }

        return $name;
    }

    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions()
    {
        $websiteCollection = $this->systemStore->getWebsiteCollection();
        $groupCollection = $this->systemStore->getGroupCollection();
        $storeCollection = $this->systemStore->getStoreCollection();
        /** @var \Magento\Store\Model\Website $website */
        foreach ($websiteCollection as $website) {
            $groups = [];
            /** @var \Magento\Store\Model\Group $group */
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $stores = [];
                    /** @var  \Magento\Store\Model\Store $store */
                    foreach ($storeCollection as $store) {
                        if ($store->getGroupId() == $group->getId()) {
                            $name = $this->sanitizeName($store->getName());
                            $stores[$name]['label'] = str_repeat(' ', 8) . $name;
                            $stores[$name]['value'] = $store->getId();
                        }
                    }
                    if (!empty($stores)) {
                        $name = $this->sanitizeName($group->getName());
                        $groups[$name]['label'] = str_repeat(' ', 4) . $name;
                        $groups[$name]['value'] = array_values($stores);
                    }
                }
            }
            if (!empty($groups)) {
                $name = $this->sanitizeName($website->getName());
                $this->currentOptions[$name]['label'] = $name;
                $this->currentOptions[$name]['value'] = array_values($groups);
            }
        }
    }
}
