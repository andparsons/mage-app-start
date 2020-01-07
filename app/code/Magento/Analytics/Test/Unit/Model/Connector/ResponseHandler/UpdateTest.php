<?php
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\ResponseHandler\Update;

class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $updateHandler = new Update();
        $this->assertTrue($updateHandler->handleResponse([]));
    }
}
