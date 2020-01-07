<?php
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

use Magento\Framework\ObjectManagerInterface;

/**
 * Schema DTO element factory.
 */
class SchemaFactory
{
    /**
     * @var string
     */
    private $instanceName = Schema::class;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * SchemaFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters.
     *
     * @return \Magento\Framework\Setup\Declaration\Schema\Dto\Schema
     */
    public function create()
    {
        return $this->objectManager->create($this->instanceName);
    }
}
