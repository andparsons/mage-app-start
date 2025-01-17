<?php
namespace Magento\Config\Test\Unit\Model\Config\Backend\Email;

use Magento\Config\Model\Config\Backend\Email\Sender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Sender
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Sender::class);
    }

    /**
     * @dataProvider beforeSaveDataProvider
     * @param string|null $value
     * @param string|bool $expectedValue false if exception to be thrown
     * @return void
     */
    public function testBeforeSave($value, $expectedValue)
    {
        $this->model->setValue($value);

        if ($expectedValue === false) {
            $this->expectException(LocalizedException::class);
        }

        $this->model->beforeSave();

        $this->assertEquals($expectedValue, $this->model->getValue());
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            ['Mr. Real Name', 'Mr. Real Name'],
            [str_repeat('a', 256), false],
            [null, false],
            ['', false],
        ];
    }
}
