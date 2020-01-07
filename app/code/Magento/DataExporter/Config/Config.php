<?php
declare(strict_types=1);

namespace Magento\DataExporter\Config;

use Magento\Framework\Config\DataInterface;

/**
 * Config of ReportXml
 */
class Config implements ConfigInterface
{
    /**
     * @var DataInterface
     */
    private $data;

    private static $scalars = [
        'ID', 'Int', 'Float', 'String', 'Boolean'
    ];

    /**
     * Config constructor.
     *
     * @param DataInterface $data
     */
    public function __construct(
        DataInterface $data
    ) {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function get(string $profileName) : array
    {
        return $this->data->get($profileName);
    }

    /**
     * @inheritDoc
     */
    public function isScalar(string $typeName): bool
    {
        return in_array($typeName, self::$scalars);
    }
}
