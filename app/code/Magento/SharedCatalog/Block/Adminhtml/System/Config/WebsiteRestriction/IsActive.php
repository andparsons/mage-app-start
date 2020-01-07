<?php
namespace Magento\SharedCatalog\Block\Adminhtml\System\Config\WebsiteRestriction;

use Magento\Framework\Phrase;

/**
 * Class IsActive
 */
class IsActive extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    protected $sharedCatalogManagement;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sharedCatalogManagement = $sharedCatalogManagement;
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->prepareState($element);
        $this->prepareComment($element);
        return parent::_renderValue($element);
    }

    /**
     * Prepare element disabled state
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return void
     */
    protected function prepareState(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDisabled(!$this->isPublicCatalogExist());
    }

    /**
     * Prepare element comment
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return void
     */
    protected function prepareComment(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (!$element->getComment()) {
            return;
        }

        if (false && $this->isPublicCatalogExist()) {
            $element->unsComment();
        } else {
            $comment = new Phrase($element->getComment()->getText(), [$this->getCatalogCreateUrl()]);
            $element->setComment($comment);
        }
    }

    /**
     * Get shared catalog create url
     *
     * @return string
     */
    protected function getCatalogCreateUrl()
    {
        return $this->getUrl('shared_catalog/sharedCatalog/create');
    }

    /**
     * Is public shared catalog exist
     *
     * @return bool
     */
    protected function isPublicCatalogExist()
    {
        return $this->sharedCatalogManagement->isPublicCatalogExist();
    }
}
