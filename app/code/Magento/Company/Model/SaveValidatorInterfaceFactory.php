<?php

namespace Magento\Company\Model;

/**
 * Company save validator interface factory.
 */
class SaveValidatorInterfaceFactory
{
    /**
     * Object Manager instance.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters.
     *
     * @param string $className
     * @param array $data [optional]
     * @return SaveValidatorInterface
     */
    public function create($className, array $data = [])
    {
        return $this->objectManager->create($className, $data);
    }
}
