<?php
declare(strict_types=1);

namespace Magento\Framework\Encryption;

class ModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Encryption\Encryptor::class
        );
    }

    public function testEncryptDecrypt()
    {
        $encryptor = $this->_model;

        $this->assertEquals('', $encryptor->decrypt($encryptor->encrypt('')));
        $this->assertEquals('test', $encryptor->decrypt($encryptor->encrypt('test')));
    }

    public function testEncryptDecrypt2()
    {
        $encryptor = $this->_model;

        $initial = md5(uniqid());
        $encrypted = $encryptor->encrypt($initial);
        $this->assertNotEquals($initial, $encrypted);
        $this->assertEquals($initial, $encryptor->decrypt($encrypted));
    }

    public function testValidateKey()
    {
        $validKey = md5(uniqid());
        $this->_model->validateKey($validKey);
    }

    /**
     * @expectedException \Exception
     */
    public function testValidateKeyInvalid()
    {
        $invalidKey = '----    ';
        $this->_model->validateKey($invalidKey);
    }

    public function testGetValidateHash()
    {
        $password = uniqid();
        $hash = $this->_model->getHash($password, true);

        $this->assertTrue(is_string($hash));
        $this->assertTrue($this->_model->validateHash($password, $hash));
    }
}
