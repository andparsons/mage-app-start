<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard;

/**
 * Class StepsWizard
 *
 * @api
 * @since 100.0.0
 */
class StepsWizard extends \Magento\Ui\Block\Component\StepsWizard
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * SharedCatalogManagement constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Get wizard init data in JSON
     * @return string
     */
    public function getInitDataAsJson()
    {
        return $this->jsonEncoder->encode($this->getInitData());
    }

    /**
     * Get wizard step components in JSON
     * @return string
     */
    public function getStepComponentsAsJson()
    {
        return $this->jsonEncoder->encode($this->getStepComponents());
    }
}
