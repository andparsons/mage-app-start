<?php

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestSetupDeclarationModule6') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestSetupDeclarationModule6', __DIR__);
}
