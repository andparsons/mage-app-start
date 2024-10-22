<?php

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Phrase;

class AuthorizationExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $authorizationException = new AuthorizationException(
            new Phrase(
                'The consumer isn\'t authorized to access %resources.',
                ['consumer_id' => 1, 'resources' => 'record2']
            )
        );
        $this->assertSame("The consumer isn't authorized to access record2.", $authorizationException->getMessage());
    }
}
