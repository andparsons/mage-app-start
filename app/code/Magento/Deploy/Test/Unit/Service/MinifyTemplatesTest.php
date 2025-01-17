<?php
namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Service\MinifyTemplates;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\View\Template\Html\MinifierInterface;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Minify Templates service class unit tests
 */
class MinifyTemplatesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MinifyTemplates
     */
    private $service;

    /**
     * @var Files|Mock
     */
    private $filesUtils;

    /**
     * @var MinifierInterface|Mock
     */
    private $htmlMinifier;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->filesUtils = $this->createPartialMock(Files::class, ['getPhtmlFiles']);

        $this->htmlMinifier = $this->getMockForAbstractClass(
            MinifierInterface::class,
            ['minify'],
            '',
            false
        );

        $this->service = new MinifyTemplates(
            $this->filesUtils,
            $this->htmlMinifier
        );
    }

    /**
     * @see MinifyTemplates::minifyTemplates()
     */
    public function testMinifyTemplates()
    {
        $templateMock = "template.phtml";
        $templatesMock = [$templateMock];

        $this->filesUtils->expects($this->once())
            ->method('getPhtmlFiles')
            ->with(false, false)
            ->willReturn($templatesMock);

        $this->htmlMinifier->expects($this->once())->method('minify')->with($templateMock);

        $this->assertEquals(1, $this->service->minifyTemplates());
    }
}
