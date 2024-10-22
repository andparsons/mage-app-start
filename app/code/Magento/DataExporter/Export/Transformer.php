<?php
declare(strict_types=1);

namespace Magento\DataExporter\Export;

use Magento\DataExporter\Config\ConfigInterface;
use Magento\DataExporter\Export\Request\Info;

/**
 * Class Transformer
 */
class Transformer
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Transformer constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Check if type is scalar
     *
     * @param string $typeName
     * @return bool
     */
    private function isScalar(string $typeName) : bool
    {
        return $this->config->isScalar($typeName);
    }

    /**
     * Transform info data
     *
     * @param Info $info
     * @param array $snapshot
     * @return array
     */
    public function transform(Info $info, array $snapshot) : array
    {
        $result = [];
        $key = $this->getKey($info->getRootNode()->getField());
        if (!isset($snapshot[$key])) {
            return $result;
        }
        $data = $this->convertComplexData(
            $info->getRootNode()->getField(),
            $snapshot,
            null
        );
        return $data ? $data : [];
    }

    /**
     * Get field key
     *
     * @param array $field
     * @return string
     */
    private function getKey(array $field) : string
    {
        return base64_encode(json_encode($field));
    }

    /**
     * Cast scalar value
     *
     * @param string $type
     * @param $value
     * @return bool|float|int|string|null
     */
    private function castScalarValue(string $type, $value)
    {
        $result = null;
        switch ($type) {
            case "ID":
            case "String":
                $result = (string) $value;
                break;
            case "Int":
                $result = (int) $value;
                break;
            case "Float":
                $result = (float) $value;
                break;
            case "Boolean":
                $result = (bool) $value;
                break;
        }
        return $result;
    }

    /**
     * Cast field to type
     *
     * @param array $rootField
     * @param $value
     * @return bool|float|int|string|null
     */
    private function castToFieldType(array $rootField, $value)
    {
        $result = null;
        if ($this->isScalar($rootField['type'])) {
            if ($rootField['repeated']) {
                if (is_array($value)) {
                    for ($i = 0; count($value) > $i; $i++) {
                        $result[$i] = $this->castScalarValue($rootField['type'], $value[$i]);
                    }
                }
            } else {
                $result = $this->castScalarValue($rootField['type'], $value);
            }
        } else {
            $type = $this->config->get($rootField['type']);
            if ($rootField['repeated']) {
                if (is_array($value)) {
                    for ($i=0; count($value) > $i; $i++) {
                        foreach ($type['field'] as $field) {
                            if (isset($value[$i][$field['name']])) {
                                $result[$i][$field['name']] = $this->castToFieldType($field, $value[$i][$field['name']]);
                            } else {
                                $result[$i][$field['name']] = null;
                            }
                        }
                    }
                }
            } else {
                foreach ($type['field'] as $field) {
                    if (isset($value[$field['name']])) {
                        $result[$field['name']] = $this->castToFieldType($field, $value[$field['name']]);
                    } else {
                        $result[$field['name']] = null;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Convert complex row
     *
     * @param array $row
     * @param array $type
     * @param array $snapshot
     * @return array
     */
    private function convertComplexRow(array $row, array $type, array $snapshot) : array
    {
        $result = [];
        foreach ($type['field'] as $field) {
            if ($field['provider'] != null) {
                $key = $this->getKey($field);
                if (isset($snapshot[$key])) {
                    $index = [];
                    foreach ($field['using'] as $key) {
                        $index[] = [$key['field'] => $row[$key['field']]];
                    }
                    $lookupReference = base64_encode(json_encode($index));
                    //todo: add Filter cond
                    $result[$field['name']] = $this->convertComplexData($field, $snapshot, $lookupReference);
                }
            } else {
                if (isset($row[$field['name']])) {
                    $result[$field['name']] = $this->castToFieldType($field, $row[$field['name']]);
                }
            }
        }
        return $result;
    }

    /**
     * Convert complex data
     *
     * @param array $field
     * @param array $snapshot
     * @param string $lookup
     * @return array
     */
    private function convertComplexData(array $field, array $snapshot, ?string $lookup)
    {
        if ($lookup) {
            if (!isset($snapshot[$this->getKey($field)][$lookup])) {
                return null;
            }
            $data = $snapshot[$this->getKey($field)][$lookup];
        } else {
            $data = $snapshot[$this->getKey($field)];
        }
        $result = null;
        if ($this->isScalar($field['type'])) {
            $result = $this->castToFieldType($field, $data);
        } else {
            $type = $this->config->get($field['type']);
            if ($field['repeated']) {
                for ($i=0; $i < count($data); $i++) {
                    $result[$i] = $this->convertComplexRow($data[$i], $type, $snapshot);
                }
            } else {
                $result = $this->convertComplexRow($data, $type, $snapshot);
            }
        }
        return $result;
    }
}
