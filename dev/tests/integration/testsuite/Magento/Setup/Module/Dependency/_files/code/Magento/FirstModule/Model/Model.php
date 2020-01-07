<?php
namespace Magento\FirstModule\Model;

use Magento\LibSecond;

class Model
{
    /**
     * @use Magento\LibSecond()
     */
    public function test()
    {
        new LibSecond();
    }
}
