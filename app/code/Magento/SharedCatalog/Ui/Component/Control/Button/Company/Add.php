<?php
namespace Magento\SharedCatalog\Ui\Component\Control\Button\Company;

/**
 * Class Add
 */
class Add extends \Magento\Ui\Component\Control\Button
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    protected $sharedCatalogManagement;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sharedCatalogManagement = $sharedCatalogManagement;
    }

    /**
     * Get button disabled state
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisabled()
    {
        return !$this->sharedCatalogManagement->isPublicCatalogExist();
    }
}
