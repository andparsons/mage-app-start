<?php
namespace Magento\SomeModule\Model;

class DoubleColon
{
    public function __construct()
    {
        DoubleColon::class;
    }

    public function method()
    {
    }
}
