<?php
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Cryptographer;
use Magento\Analytics\Model\EncodedContext;
use Magento\Analytics\Model\EncodedContextFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CryptographerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var EncodedContextFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encodedContextFactoryMock;

    /**
     * @var EncodedContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encodedContextMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Cryptographer
     */
    private $cryptographer;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $initializationVectors;

    /**
     * @var
     */
    private $source;

    /**
     * @var string
     */
    private $cipherMethod = 'AES-256-CBC';

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->analyticsTokenMock = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->encodedContextFactoryMock = $this->getMockBuilder(EncodedContextFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->encodedContextMock = $this->getMockBuilder(EncodedContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->key = '';
        $this->source = '';
        $this->initializationVectors = [];

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->cryptographer = $this->objectManagerHelper->getObject(
            Cryptographer::class,
            [
                'analyticsToken' => $this->analyticsTokenMock,
                'encodedContextFactory' => $this->encodedContextFactoryMock,
                'cipherMethod' => $this->cipherMethod,
            ]
        );
    }

    /**
     * @return void
     */
    public function testEncode()
    {
        $token = 'some-token-value';
        $this->source = 'Some text';
        $this->key = hash('sha256', $token);

        $checkEncodedContext = function ($parameters) {
            $emptyRequiredParameters =
                array_diff(['content', 'initializationVector'], array_keys(array_filter($parameters)));
            if ($emptyRequiredParameters) {
                return false;
            }

            $encryptedData = openssl_encrypt(
                $this->source,
                $this->cipherMethod,
                $this->key,
                OPENSSL_RAW_DATA,
                $parameters['initializationVector']
            );

            return ($encryptedData === $parameters['content']);
        };

        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('getToken')
            ->with()
            ->willReturn($token);

        $this->encodedContextFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback($checkEncodedContext))
            ->willReturn($this->encodedContextMock);

        $this->assertSame($this->encodedContextMock, $this->cryptographer->encode($this->source));
    }

    /**
     * @return void
     */
    public function testEncodeUniqueInitializationVector()
    {
        $this->source = 'Some text';
        $token = 'some-token-value';

        $registerInitializationVector = function ($parameters) {
            if (empty($parameters['initializationVector'])) {
                return false;
            }

            $this->initializationVectors[] = $parameters['initializationVector'];

            return true;
        };

        $this->analyticsTokenMock
            ->expects($this->exactly(2))
            ->method('getToken')
            ->with()
            ->willReturn($token);

        $this->encodedContextFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->with($this->callback($registerInitializationVector))
            ->willReturn($this->encodedContextMock);

        $this->assertSame($this->encodedContextMock, $this->cryptographer->encode($this->source));
        $this->assertSame($this->encodedContextMock, $this->cryptographer->encode($this->source));
        $this->assertCount(2, array_unique($this->initializationVectors));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider encodeNotValidSourceDataProvider
     */
    public function testEncodeNotValidSource($source)
    {
        $this->cryptographer->encode($source);
    }

    /**
     * @return array
     */
    public function encodeNotValidSourceDataProvider()
    {
        return [
            'Array' => [[]],
            'Empty string' => [''],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEncodeNotValidCipherMethod()
    {
        $source = 'Some string';
        $cryptographer = $this->objectManagerHelper->getObject(
            Cryptographer::class,
            [
                'cipherMethod' => 'Wrong-method',
            ]
        );

        $cryptographer->encode($source);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testEncodeTokenNotValid()
    {
        $source = 'Some string';

        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('getToken')
            ->with()
            ->willReturn(null);

        $this->cryptographer->encode($source);
    }
}
