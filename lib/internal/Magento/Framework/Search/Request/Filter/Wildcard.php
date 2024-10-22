<?php
namespace Magento\Framework\Search\Request\Filter;

use Magento\Framework\Search\AbstractKeyValuePair;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Wildcard Filter
 * @api
 * @since 100.0.2
 */
class Wildcard extends AbstractKeyValuePair implements FilterInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @param string $name
     * @param string|array $value
     * @param string $field
     * @codeCoverageIgnore
     */
    public function __construct($name, $value, $field)
    {
        parent::__construct($name, $value);
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return FilterInterface::TYPE_WILDCARD;
    }

    /**
     * Get Field
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getField()
    {
        return $this->field;
    }
}
