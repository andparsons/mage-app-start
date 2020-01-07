<?php
namespace Magento\Company\Ui\Component\Form\Field;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class CustomerGroup.
 */
class CustomerGroup extends \Magento\Company\Ui\Component\Form\Field
{
    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param array|\Magento\Framework\View\Element\UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        array $components,
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->groupManagement = $groupManagement;
    }

    /**
     * Get field config default data.
     *
     * @return array
     */
    protected function getConfigDefaultData()
    {
        return [
            'value' => $this->getDefaultValue()
        ];
    }

    /**
     * Get default field value.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultValue()
    {
        return $this->groupManagement->getDefaultGroup()->getId();
    }
}
