<?php

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestSetupDeclarationModule4') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestSetupDeclarationModule4', __DIR__);
}
