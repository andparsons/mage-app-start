<?php

namespace Magento\Framework\Controller\Test\Unit\Result;

/**
 * Class JsonTest
 *
 * @covers \Magento\Framework\Controller\Result\Json
 */
class JsonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testRenderResult()
    {
        $json = '{"data":"data"}';
        $translatedJson = '{"data_translated":"data_translated"}';

        /** @var \Magento\Framework\Translate\InlineInterface|\PHPUnit_Framework_MockObject_MockObject
         * $translateInline
         */
        $translateInline = $this->createMock(\Magento\Framework\Translate\InlineInterface::class);
        $translateInline->expects($this->any())->method('processResponseBody')->with($json, true)->will(
            $this->returnValue($translatedJson)
        );

        $response = $this->createMock(\Magento\Framework\App\Response\HttpInterface::class);
        $response->expects($this->atLeastOnce())->method('setHeader')->with('Content-Type', 'application/json', true);
        $response->expects($this->atLeastOnce())->method('setBody')->with($json);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\Controller\Result\Json::class, ['translateInline' => $translateInline]);
        $resultJson->setJsonData($json);
        $this->assertSame($resultJson, $resultJson->renderResult($response));
    }
}
