<?php
namespace Magento\Framework\View\Asset\File;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Magento\Framework\View\Asset\File\FallbackContext
 */
class FallbackContextFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\View\Asset\File\FallbackContext
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(FallbackContext::class, $data);
    }
}
