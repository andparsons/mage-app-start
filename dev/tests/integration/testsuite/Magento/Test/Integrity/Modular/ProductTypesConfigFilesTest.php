<?php
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\Component\ComponentRegistrar;

class ProductTypesConfigFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config\Reader
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $moduleDirSearch \Magento\Framework\Component\DirSearch */
        $moduleDirSearch = $objectManager->get(\Magento\Framework\Component\DirSearch::class);
        $fileIteratorFactory = $objectManager->get(\Magento\Framework\Config\FileIteratorFactory::class);
        $xmlFiles = $fileIteratorFactory->create(
            $moduleDirSearch->collectFiles(ComponentRegistrar::MODULE, 'etc/{*/product_types.xml,product_types.xml}')
        );

        $fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($xmlFiles));
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->expects($this->any())->method('isValidationRequired')->will($this->returnValue(true));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create(
            \Magento\Catalog\Model\ProductTypes\Config\Reader::class,
            ['fileResolver' => $fileResolverMock, 'validationState' => $validationStateMock]
        );
    }

    public function testProductTypesXmlFiles()
    {
        $this->_model->read('global');
    }
}
