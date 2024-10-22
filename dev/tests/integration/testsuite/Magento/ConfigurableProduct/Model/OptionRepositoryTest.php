<?php
namespace Magento\ConfigurableProduct\Model;

class OptionRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation disabled
     */
    public function testGetListWithExtensionAttributes()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $productSku = 'configurable';
        /** @var \Magento\ConfigurableProduct\Api\OptionRepositoryInterface $optionRepository */
        $optionRepository = $objectManager->create(\Magento\ConfigurableProduct\Api\OptionRepositoryInterface::class);

        $options = $optionRepository->getList($productSku);
        $this->assertCount(1, $options, "Invalid number of option.");
        $this->assertNotNull($options[0]->getExtensionAttributes(), "Extension attributes not loaded");
        /** @var \Magento\Eav\Model\Entity\Attribute $joinedEntity */
        $joinedEntity = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
        $joinedEntity->load($options[0]->getId());
        $joinedExtensionAttributeValue = $joinedEntity->getAttributeCode();
        $result = $options[0]->getExtensionAttributes()->__toArray();
        $this->assertEquals(
            $joinedExtensionAttributeValue,
            $result['test_dummy_attribute'],
            "Extension attributes were not loaded correctly"
        );
    }
}
