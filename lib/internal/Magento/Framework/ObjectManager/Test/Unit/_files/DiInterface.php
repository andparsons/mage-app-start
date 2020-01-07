<?php
namespace Magento\Test\Di;

interface DiInterface
{
    /**
     * @param string $param
     * @return mixed
     */
    public function wrap($param);
}
