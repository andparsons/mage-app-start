<?php
namespace Magento\Setup\Test\Unit\Model\Description;

class DescriptionSentenceGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Dictionary
     */
    private $dictionaryMock;

    /**
     * @var \Magento\Setup\Model\Description\DescriptionSentenceGenerator
     */
    private $sentenceGenerator;

    /**
     * @var array
     */
    private $sentenceConfig = [
        'words' => [
            'count-min' => 7,
            'count-max' => 7
        ]
    ];

    public function setUp()
    {
        $this->dictionaryMock = $this->createMock(\Magento\Setup\Model\Dictionary::class);
        $this->sentenceGenerator = new \Magento\Setup\Model\Description\DescriptionSentenceGenerator(
            $this->dictionaryMock,
            $this->sentenceConfig
        );
    }

    public function testSentenceGeneration()
    {
        $this->dictionaryMock
            ->expects($this->exactly(7))
            ->method('getRandWord')
            ->will($this->onConsecutiveCalls(
                'Lorem',
                'ipsum',
                'dolor',
                'sit',
                'amet',
                'consectetur',
                'adipiscing'
            ));

        $this->assertEquals(
            'Lorem ipsum dolor sit amet consectetur adipiscing.',
            $this->sentenceGenerator->generate()
        );
    }
}
