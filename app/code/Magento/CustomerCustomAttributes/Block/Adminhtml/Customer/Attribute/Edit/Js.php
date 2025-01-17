<?php

/**
 * Customer and Customer Address Attributes Edit JavaScript Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CustomerCustomAttributes\Block\Adminhtml\Customer\Attribute\Edit;

/**
 * @api
 * @since 100.0.2
 */
class Js extends \Magento\Backend\Block\Template
{
    /**
     * Customer data
     *
     * @var \Magento\CustomerCustomAttributes\Helper\Data
     */
    protected $_customerData = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\CustomerCustomAttributes\Helper\Data $customerData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\CustomerCustomAttributes\Helper\Data $customerData,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_customerData = $customerData;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve allowed Input Validate Filters in JSON format
     *
     * @return string
     */
    public function getValidateFiltersJson()
    {
        return $this->_jsonEncoder->encode($this->_customerData->getAttributeValidateFilters());
    }

    /**
     * Retrieve allowed Input Filter Types in JSON format
     *
     * @return string
     */
    public function getFilteTypesJson()
    {
        return $this->_jsonEncoder->encode($this->_customerData->getAttributeFilterTypes());
    }

    /**
     * Returns array of input types with type properties
     *
     * @return array
     */
    public function getAttributeInputTypes()
    {
        return $this->_customerData->getAttributeInputTypes();
    }
}
