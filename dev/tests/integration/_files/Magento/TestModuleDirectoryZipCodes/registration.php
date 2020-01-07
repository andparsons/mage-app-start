<?php

use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleDirectoryZipCodes') === null) {
    ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magento_TestModuleDirectoryZipCodes', __DIR__);
}
