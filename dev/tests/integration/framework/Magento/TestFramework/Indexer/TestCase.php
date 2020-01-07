<?php
namespace Magento\TestFramework\Indexer;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function tearDownAfterClass()
    {
        $db = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();
    }
}
