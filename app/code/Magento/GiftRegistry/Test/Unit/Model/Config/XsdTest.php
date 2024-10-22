<?php
namespace Magento\GiftRegistry\Test\Unit\Model\Config;

class XsdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * File path for xsd
     *
     * @var string
     */
    protected $_xsdFilePath;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->_xsdFilePath = $urnResolver->getRealPath('urn:magento:module:Magento_GiftRegistry:etc/giftregistry.xsd');
    }

    /**
     * Tests different cases with invalid xml files
     *
     * @dataProvider invalidXmlFileDataProvider
     * @param string $xmlFile
     * @param array $expectedErrors
     */
    public function testInvalidXmlFile($xmlFile, $expectedErrors)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . '/../_files/' . $xmlFile);

        libxml_use_internal_errors(true);
        $errorMessages = \Magento\Framework\Config\Dom::validateDomDocument($dom, $this->_xsdFilePath);
        libxml_use_internal_errors(false);

        $this->assertEquals($errorMessages, $expectedErrors);
    }

    /**
     * Tests valid xml file
     */
    public function testValidXmlFile()
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . '/../_files/config_valid.xml');

        libxml_use_internal_errors(true);
        $errorMessages = \Magento\Framework\Config\Dom::validateDomDocument($dom, $this->_xsdFilePath);
        libxml_use_internal_errors(false);

        $this->assertEmpty($errorMessages);
    }

    /**
     * @return array
     */
    public function invalidXmlFileDataProvider()
    {
        return [
            [
                'config_invalid_attribute_group.xml',
                [
                    "Element 'attribute_group': Duplicate key-sequence ['registry'] " .
                    "in unique identity-constraint 'uniqueAttributeGroupName'.\nLine: 17\n"
                ],
            ],
            [
                'config_invalid_attribute_type.xml',
                [
                    "Element 'attribute_type': Duplicate key-sequence ['text'] " .
                    "in unique identity-constraint 'uniqueAttributeTypeName'.\nLine: 12\n"
                ]
            ],
            [
                'config_invalid_static_attribute.xml',
                [
                    "Element 'static_attribute': Duplicate key-sequence ['event_date'] " .
                    "in unique identity-constraint 'uniqueStaticAttributeName'.\nLine: 20\n"
                ]
            ],
            [
                'config_invalid_custom_attribute.xml',
                [
                    "Element 'custom_attribute': Duplicate key-sequence ['custom_event_data'] " .
                    "in unique identity-constraint 'uniqueCustomAttributeName'.\nLine: 23\n"
                ]
            ]
        ];
    }
}
