<?php
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductAttributeTypeTest extends GraphQlAbstract
{
    /**
     * Verify the schema returns correct attribute type , given the attributeCode and corresponding entityType
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttributeTypeResolver()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"description",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"status",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"special_price",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"disable_auto_group_change",
      entity_type:"customer"
    }
    {
      attribute_code:"special_price",
      entity_type:"Magento\\\\Catalog\\\\Api\\\\Data\\\\ProductInterface"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type      
    } 
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeCodes = [
            'description',
            'status',
            'special_price',
            'disable_auto_group_change',
            'special_price'
        ];
        $entityType = [
            'catalog_product',
            'catalog_product',
            'catalog_product',
            'customer',
            \Magento\Catalog\Api\Data\ProductInterface::class
        ];
        $attributeTypes = ['String', 'Int', 'Float','Boolean', 'Float'];
        $this->assertAttributeType($attributeTypes, $expectedAttributeCodes, $entityType, $response);
    }

    /**
     * Verify that the complex EAV attributes types are resolved
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testComplexAttributeTypeResolver()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"default_sort_by",
      entity_type:"catalog_category"
    },
    {
      attribute_code:"available_sort_by",
      entity_type:"catalog_category"
    },
    {
      attribute_code:"store_id",
      entity_type:"customer"
    },
    {
      attribute_code:"quantity_and_stock_status",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"default_billing",
      entity_type:"customer"
    },
    {
     attribute_code:"region"
     entity_type:"customer_address"
    },
    {
      attribute_code:"media_gallery",
      entity_type:"catalog_product"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type      
    } 
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeCodes = [
            'default_sort_by',
            'available_sort_by',
            'store_id',
            'quantity_and_stock_status',
            'default_billing',
            'region',
            'media_gallery'
        ];
        $entityTypes = [
            'catalog_category',
            'catalog_category',
            'customer',
            'catalog_product',
            'customer',
            'customer_address',
            'catalog_product'
        ];
        $attributeTypes = [
            'String[]',
            'String[]',
            'Int',
            'CatalogInventoryDataStockItemInterface[]',
            'CustomerDataAddressInterface',
            'CustomerDataRegionInterface',
            'ProductMediaGallery'
        ];
        $this->assertAttributeType($attributeTypes, $expectedAttributeCodes, $entityTypes, $response);
    }

    /**
     * Verify the schema returns attribute type as AnyType
     *
     * For undefined attributes and for attributes with no backendModel mapping available
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testUnDefinedAttributeType()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"undefine_attribute",
      entity_type:"catalog_category"
    },
    {
      attribute_code:"special_price",
      entity_type:"customer"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type      
    } 
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeCodes = ['undefine_attribute', 'special_price'];
        $entityTypes = ['catalog_category', 'customer'];
        $attributeTypes = ['AnyType'];
        $attributeMetaData = array_map(null, $response['customAttributeMetadata']['items'], $entityTypes);
        foreach ($attributeMetaData as $itemsIndex => $itemArray) {
            $this->assertResponseFields(
                $attributeMetaData[$itemsIndex][0],
                [
                  "attribute_code" => $expectedAttributeCodes[$itemsIndex],
                  "attribute_type" =>$attributeTypes[0],
                  "entity_type" => $entityTypes[$itemsIndex]
                ]
            );
        }
    }

    /**
     * @param array $attributeTypes
     * @param array $expectedAttributeCodes
     * @param array $entityTypes
     * @param array $actualResponse
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function assertAttributeType($attributeTypes, $expectedAttributeCodes, $entityTypes, $actualResponse)
    {
        $attributeMetaDataItems = array_map(null, $actualResponse['customAttributeMetadata']['items'], $attributeTypes);

        foreach ($attributeMetaDataItems as $itemIndex => $itemArray) {
            $this->assertResponseFields(
                $attributeMetaDataItems[$itemIndex][0],
                [
                    "attribute_code" => $expectedAttributeCodes[$itemIndex],
                    "attribute_type" =>$attributeTypes[$itemIndex],
                    "entity_type" => $entityTypes[$itemIndex]
                ]
            );
        }
    }
}
