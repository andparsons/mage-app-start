<?php chdir('/Users/andrew/Sites/mage-app-start');
require __DIR__ . '/../../app/bootstrap.php';
$bootstrap = Magento\Framework\App\Bootstrap::create(BP, $_SERVER);echo 'MAGICENTO2';