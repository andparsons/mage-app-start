<?php

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModule3') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModule3', __DIR__);
}
