<?php
namespace Magento\Framework\View\Design\Fallback\Rule;

use Magento\Framework\ObjectManagerInterface;

class ModularSwitchFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create rule instance
     *
     * @param array $data
     * @return \Magento\Framework\View\Design\Fallback\Rule\Simple
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(\Magento\Framework\View\Design\Fallback\Rule\ModularSwitch::class, $data);
    }
}
