<?php
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader;

use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\FieldMetaReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DocReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\ImplementsReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\CacheAnnotationReader;

/**
 * Composite configuration reader to handle the object type meta
 */
class ObjectType implements TypeMetaReaderInterface
{
    /**
     * @var FieldMetaReader
     */
    private $fieldMetaReader;

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @var ImplementsReader
     */
    private $implementsAnnotation;

    /**
     * @var CacheAnnotationReader
     */
    private $cacheAnnotationReader;

    /**
     * ObjectType constructor.
     * @param FieldMetaReader $fieldMetaReader
     * @param DocReader $docReader
     * @param ImplementsReader $implementsAnnotation
     * @param CacheAnnotationReader|null $cacheAnnotationReader
     */
    public function __construct(
        FieldMetaReader $fieldMetaReader,
        DocReader $docReader,
        ImplementsReader $implementsAnnotation,
        CacheAnnotationReader $cacheAnnotationReader = null
    ) {
        $this->fieldMetaReader = $fieldMetaReader;
        $this->docReader = $docReader;
        $this->implementsAnnotation = $implementsAnnotation;
        $this->cacheAnnotationReader = $cacheAnnotationReader ?? \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CacheAnnotationReader::class);
    }

    /**
     * @inheritdoc
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\ObjectType) {
            $typeName = $typeMeta->name;
            $result = [
                'name' => $typeName,
                'type' => 'graphql_type',
                'fields' => [], // Populated later
            ];

            $interfacesNames = $this->implementsAnnotation->read($typeMeta->astNode->directives);
            foreach ($interfacesNames as $interfaceName) {
                $result['implements'][$interfaceName] = [
                    'interface' => $interfaceName,
                    'copyFields' => true
                ];
            }

            $fields = $typeMeta->getFields();
            foreach ($fields as $fieldName => $fieldMeta) {
                $result['fields'][$fieldName] = $this->fieldMetaReader->read($fieldMeta);
            }

            if ($this->docReader->read($typeMeta->astNode->directives)) {
                    $result['description'] = $this->docReader->read($typeMeta->astNode->directives);
            }

            if ($this->docReader->read($typeMeta->astNode->directives)) {
                $result['cache'] = $this->cacheAnnotationReader->read($typeMeta->astNode->directives);
            }

            return $result;
        } else {
            return [];
        }
    }

    /**
     * Find interface graphql type in array list of strings
     *
     * @param \GraphQL\Type\Definition\InterfaceType $interfacesType
     * @param string[] $interfacesNames
     * @return bool
     */
    public function isInInterfaceTypeInList(
        \GraphQL\Type\Definition\InterfaceType  $interfacesType,
        array $interfacesNames
    ) : bool {
        if (in_array($interfacesType->name, $interfacesNames)) {
            return true;
        } else {
            return false;
        }
    }
}
