<?php

namespace Magento\PageCache\Test\Unit\Model\App\Response;

use Magento\PageCache\Model\App\Response\HttpPlugin;

class HttpPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param \Magento\Framework\App\Response\FileInterface $responseInstanceClass
     * @param int $sendVaryCalled
     *
     * @dataProvider beforeSendResponseDataProvider
     */
    public function testBeforeSendResponse($responseInstanceClass, $sendVaryCalled)
    {
        /** @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject $responseMock */
        $responseMock = $this->createMock($responseInstanceClass);
        $responseMock->expects($this->exactly($sendVaryCalled))
            ->method('sendVary');
        $plugin = new HttpPlugin();
        $plugin->beforeSendResponse($responseMock);
    }

    /**
     * @return array
     */
    public function beforeSendResponseDataProvider()
    {
        return [
            [\Magento\Framework\App\Response\Http::class, 1],
            [\Magento\MediaStorage\Model\File\Storage\Response::class, 0]
        ];
    }
}
