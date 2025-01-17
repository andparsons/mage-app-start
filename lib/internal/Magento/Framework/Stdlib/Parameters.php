<?php
declare(strict_types=1);

namespace Magento\Framework\Stdlib;

use Zend\Stdlib\Parameters as ZendParameters;

/**
 * Class Parameters
 */
class Parameters
{
    /**
     * @var ZendParameters
     */
    private $parameters;

    /**
     * @param ZendParameters $parameters
     */
    public function __construct(
        ZendParameters $parameters
    ) {
        $this->parameters = $parameters;
    }

    /**
     * Populate from native PHP array
     *
     * @param  array $values
     * @return void
     */
    public function fromArray(array $values)
    {
        $this->parameters->fromArray($values);
    }

    /**
     * Populate from query string
     *
     * @param  string $string
     * @return void
     */
    public function fromString($string)
    {
        $this->parameters->fromString($string);
    }

    /**
     * Serialize to native PHP array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->parameters->toArray();
    }

    /**
     * Serialize to query string
     *
     * @return string
     */
    public function toString()
    {
        return $this->parameters->toString();
    }

    /**
     * Retrieve by key
     *
     * Returns null if the key does not exist.
     *
     * @param  string $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->parameters->offsetGet($name);
    }

    /**
     * Get name
     *
     * @param string $name
     * @param mixed $default optional default value
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->parameters->get($name, $default);
    }

    /**
     * Set name
     *
     * @param string $name
     * @param mixed $value
     * @return \Zend\Stdlib\Parameters
     */
    public function set($name, $value)
    {
        return $this->parameters->set($name, $value);
    }
}
