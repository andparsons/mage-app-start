<?php
use Magento\Framework\Component\ComponentRegistrar;

$registrar = new ComponentRegistrar();
if ($registrar->getPath(ComponentRegistrar::MODULE, 'Magento_TestModuleMessageQueueConfigOverride') === null) {
    ComponentRegistrar::register(
        ComponentRegistrar::MODULE,
        'Magento_TestModuleMessageQueueConfigOverride',
        __DIR__
    );
}
