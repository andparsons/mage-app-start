<?php
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * LongBlob DTO element factory.
 */
class LongBlob implements FactoryInterface
{
    /**
     * Default long blog length.
     */
    const DEFAULT_BLOB_LENGTH = 2147483648;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param string                 $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Blob::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        if (!isset($data['length'])) {
            $data['length'] = self::DEFAULT_BLOB_LENGTH;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
