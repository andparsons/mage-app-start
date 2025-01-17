<?php
namespace Magento\Staging\Model\Operation\Update;

use Magento\Framework\ObjectManagerInterface;

class PermanentUpdateProcessorPool
{
    /**
     * @var array
     */
    private $processors;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $processors
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $processors = []
    ) {
        $this->processors = $processors;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $entityType
     * @return \Magento\Staging\Model\Operation\Update\UpdateProcessorInterface
     */
    public function getProcessor($entityType)
    {
        $processorClass = isset($this->processors[$entityType])
            ? $this->processors[$entityType]
            : $this->processors['default'];
        return $this->objectManager->get($processorClass);
    }
}
