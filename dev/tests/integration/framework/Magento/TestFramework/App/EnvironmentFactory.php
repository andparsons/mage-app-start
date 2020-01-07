<?php

namespace Magento\TestFramework\App;

use Magento\TestFramework\App\ObjectManager\Environment\Developer;

class EnvironmentFactory extends \Magento\Framework\App\EnvironmentFactory
{
    public function createEnvironment()
    {
        return new Developer($this);
    }
}
