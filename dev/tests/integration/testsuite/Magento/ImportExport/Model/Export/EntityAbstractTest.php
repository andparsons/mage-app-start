<?php

/**
 * Test for abstract export model
 */
namespace Magento\ImportExport\Model\Export;

class EntityAbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Export\AbstractEntity
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();

        /** @var \Magento\TestFramework\ObjectManager  $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->getMockForAbstractClass(
            \Magento\ImportExport\Model\Export\AbstractEntity::class,
            [
                $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class),
                $objectManager->get(\Magento\Store\Model\StoreManager::class),
                $objectManager->get(\Magento\ImportExport\Model\Export\Factory::class),
                $objectManager->get(\Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory::class)
            ]
        );
    }

    /**
     * Check methods which provide ability to manage errors
     */
    public function testAddRowError()
    {
        $errorCode = 'test_error';
        $errorNum = 1;
        $errorMessage = 'Test error!';
        $this->_model->addMessageTemplate($errorCode, $errorMessage);
        $this->_model->addRowError($errorCode, $errorNum);

        $this->assertEquals(1, $this->_model->getErrorsCount());
        $this->assertEquals(1, $this->_model->getInvalidRowsCount());
        $this->assertArrayHasKey($errorMessage, $this->_model->getErrorMessages());
    }

    /**
     * Check methods which provide ability to manage writer object
     */
    public function testGetWriter()
    {
        $this->_model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class
            )
        );
        $this->assertInstanceOf(\Magento\ImportExport\Model\Export\Adapter\Csv::class, $this->_model->getWriter());
    }

    /**
     * Check that method throw exception when writer was not defined
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetWriterThrowsException()
    {
        $this->_model->getWriter();
    }

    /**
     * Test for method filterAttributeCollection
     */
    public function testFilterAttributeCollection()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );
        $collection = $this->_model->filterAttributeCollection($collection);
        /**
         * Check that disabled attributes is not existed in attribute collection
         */
        $existedAttributes = [];
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach ($collection as $attribute) {
            $existedAttributes[] = $attribute->getAttributeCode();
        }
        $disabledAttributes = $this->_model->getDisabledAttributes();
        foreach ($disabledAttributes as $attributeCode) {
            $this->assertNotContains(
                $attributeCode,
                $existedAttributes,
                'Disabled attribute "' . $attributeCode . '" existed in collection'
            );
        }
    }
}
/**
 * @codingStandardsIgnoreStart
 * Stub abstract class which provide to change protected property "$_disabledAttrs" and test methods depended on it
 */
abstract class Stub_Magento_ImportExport_Model_Export_AbstractEntity extends
    \Magento\ImportExport\Model\Export\AbstractEntity
{
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->_disabledAttrs = ['default_billing', 'default_shipping'];
    }
}
// @codingStandardsIgnoreEnd
