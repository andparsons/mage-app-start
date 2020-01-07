<?php
namespace Magento\Theme\Model\Design\Config;

class MetadataProvider implements MetadataProviderInterface
{
    /**
     * @var array
     */
    protected $metadata;

    /**
     * @param array $metadata
     */
    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function get()
    {
        return $this->metadata;
    }
}
