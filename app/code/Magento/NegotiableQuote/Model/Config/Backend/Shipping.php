<?php

namespace Magento\NegotiableQuote\Model\Config\Backend;

use Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Tax Config Shipping
 */
class Shipping extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate
     */
    protected $taxRecalculate;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param NegotiableQuoteTaxRecalculate $taxRecalculate
     * @param TaxHelper $taxHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        NegotiableQuoteTaxRecalculate $taxRecalculate,
        TaxHelper $taxHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->taxRecalculate = $taxRecalculate;
        $this->taxHelper = $taxHelper;
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged() && $this->taxHelper->getTaxBasedOn() == 'origin') {
            $this->taxRecalculate->setNeedRecalculate(true);
        }
        return parent::afterSave();
    }
}
