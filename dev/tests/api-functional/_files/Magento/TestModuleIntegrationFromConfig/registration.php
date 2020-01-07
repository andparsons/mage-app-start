<?php

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleIntegrationFromConfig') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModuleIntegrationFromConfig', __DIR__);
}
