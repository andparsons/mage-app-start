<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory;

/**
 * Test for SharedCatalogValidator model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SharedCatalogValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogValidator
     */
    private $validator;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogManagement;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogCollectionFactory;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepository;

    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassCollectionFactory;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalog;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->sharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogCollectionFactory = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeRepository = $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->taxClassCollectionFactory = $this
            ->getMockBuilder(\Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->setMethods([
                'getId', 'getName', 'getStoreId', 'getTaxClassId', 'getType', 'getAvailableTypes',
                'getCustomerGroupId'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->validator = $this->objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\SharedCatalogValidator::class,
            [
                'sharedCatalogManagement' => $this->sharedCatalogManagement,
                'sharedCatalogCollectionFactory' => $this->sharedCatalogCollectionFactory,
                'storeRepository' => $this->storeRepository,
                'taxClassCollectionFactory' => $this->taxClassCollectionFactory
            ]
        );
    }

    /**
     * Prepare TaxClassCollectionFactory mock.
     *
     * @param int $taxClassId
     * @return void
     */
    private function prepareTaxClassCollectionFactory($taxClassId)
    {
        $taxClass = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()->getMock();
        $taxClass->expects($this->exactly(2))->method('getId')->willReturn($taxClassId);

        $taxClassCollection = $this->getMockBuilder(\Magento\Tax\Model\ResourceModel\TaxClass\Collection::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMock();
        $taxClasses = [$taxClass];
        $taxClassCollection->expects($this->once())->method('getItems')->willReturn($taxClasses);

        $this->taxClassCollectionFactory->expects($this->once())->method('create')
            ->willReturn($taxClassCollection);
    }

    /**
     * Prepare SharedCatalogCollectionFactory mock.
     *
     * @param array $calls
     * @return void
     */
    private function prepareSharedCatalogCollectionFactory(array $calls)
    {
        $sharedCatalogCollection = $this
            ->getMockBuilder(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection::class)
            ->setMethods(['addFieldToFilter', 'getFirstItem'])
            ->disableOriginalConstructor()->getMock();
        $sharedCatalogCollection->expects($this->exactly($calls['sharedCatalogCollection_addFieldToFilter']))
            ->method('addFieldToFilter')->willReturnSelf();

        $sharedCatalogCollection->expects($this->exactly($calls['sharedCatalogCollection_getFirstItem']))
            ->method('getFirstItem')->willReturn($this->sharedCatalog);

        $this->sharedCatalogCollectionFactory->expects($this->exactly($calls['sharedCatalogCollectionFactory_create']))
            ->method('create')->willReturn($sharedCatalogCollection);
    }

    /**
     * Prepare validate().
     *
     * @param array $returned
     * @param array $calls
     * @return void
     */
    private function prepareValidateMethod(array $returned, array $calls)
    {
        $this->prepareTaxClassCollectionFactory($returned['taxClass_getId']);

        $sharedCatalogName = isset($returned['sharedCatalogName']) ?
            $returned['sharedCatalogName'] : 'Test Shared Catalog';
        $this->sharedCatalog->expects($this->exactly($calls['sharedCatalog_getName']))->method('getName')
            ->willReturn($sharedCatalogName);
        $storeId = 4;
        $this->sharedCatalog->expects($this->exactly($calls['sharedCatalog_getStoreId']))->method('getStoreId')
            ->willReturn($storeId);
        $this->sharedCatalog->expects($this->exactly($calls['sharedCatalog_getTaxClassId']))->method('getTaxClassId')
            ->willReturn($returned['sharedCatalog_getTaxClassId']);
        $this->sharedCatalog->expects($this->exactly($calls['sharedCatalog_getType']))->method('getType')
            ->willReturn($returned['sharedCatalog_getType']);
        $this->sharedCatalog->expects($this->exactly($calls['sharedCatalog_getAvailableTypes']))
            ->method('getAvailableTypes')->willReturn($returned['sharedCatalog_getAvailableTypes']);

        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->storeRepository->expects($this->exactly($calls['storeRepository_getById']))->method('getById')
            ->willReturn($store);

        $this->prepareSharedCatalogCollectionFactory($calls);
    }

    /**
     * Test for validate().
     *
     * @return void
     */
    public function testValidate()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 4, 'sharedCatalogCollection_getFirstItem' => 3,
            'sharedCatalogCollectionFactory_create' => 3, 'sharedCatalog_getName' => 2,
            'sharedCatalog_getTaxClassId' => 1, 'storeRepository_getById' => 1,
            'sharedCatalog_getStoreId' => 2, 'sharedCatalog_getType' => 1, 'sharedCatalog_getAvailableTypes' => 1
        ];

        $taxClassId = 2634;
        $sharedCatalogType = 5;
        $returned = [
            'sharedCatalog_getTaxClassId' => $taxClassId, 'taxClass_getId' => $taxClassId,
            'sharedCatalog_getType' => $sharedCatalogType,
            'sharedCatalog_getAvailableTypes' => [$sharedCatalogType => $sharedCatalogType],
        ];

        $sharedCatalogId = 55;
        $this->sharedCatalog->expects($this->exactly(8))->method('getId')
            ->willReturnOnConsecutiveCalls(
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                null,
                $sharedCatalogId
            );
        $customerGroupId = 56;
        $this->sharedCatalog->expects($this->exactly(3))->method('getCustomerGroupId')->willReturn($customerGroupId);

        $this->prepareValidateMethod($returned, $calls);

        $this->assertNull($this->validator->validate($this->sharedCatalog));
    }

    /**
     * Test for validate() with InputException.
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @return void
     */
    public function testValidateWithInputException()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 1, 'sharedCatalogCollection_getFirstItem' => 1,
            'sharedCatalogCollectionFactory_create' => 1, 'sharedCatalog_getName' => 1,
            'sharedCatalog_getTaxClassId' => 1, 'storeRepository_getById' => 0,
            'sharedCatalog_getStoreId' => 0, 'sharedCatalog_getType' => 0, 'sharedCatalog_getAvailableTypes' => 0
        ];

        $taxClassId = 2634;
        $sharedCatalogTaxClassId = null;
        $sharedCatalogType = 5;
        $sharedCatalogAvailableTypes = [];
        $returned = [
            'sharedCatalog_getTaxClassId' => $sharedCatalogTaxClassId, 'taxClass_getId' => $taxClassId,
            'sharedCatalog_getType' => $sharedCatalogType,
            'sharedCatalog_getAvailableTypes' => $sharedCatalogAvailableTypes,
        ];

        $sharedCatalogId = 55;
        $this->sharedCatalog->expects($this->atLeastOnce())->method('getId')
            ->willReturnOnConsecutiveCalls($sharedCatalogId, $sharedCatalogId, $sharedCatalogId, null);

        $this->prepareValidateMethod($returned, $calls);

        $this->validator->validate($this->sharedCatalog);
    }

    /**
     * Test for validate() with NoSuchEntityException.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testValidateWithNoSuchEntityException()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 1, 'sharedCatalogCollection_getFirstItem' => 1,
            'sharedCatalogCollectionFactory_create' => 1, 'sharedCatalog_getName' => 0,
            'sharedCatalog_getTaxClassId' => 0, 'storeRepository_getById' => 0,
            'sharedCatalog_getStoreId' => 0, 'sharedCatalog_getType' => 2, 'sharedCatalog_getAvailableTypes' => 1
        ];

        $taxClassId = 2634;
        $sharedCatalogType = 5;
        $sharedCatalogAvailableTypes = [7 => 7];
        $returned = [
            'sharedCatalog_getTaxClassId' => $taxClassId, 'taxClass_getId' => $taxClassId,
            'sharedCatalog_getType' => $sharedCatalogType,
            'sharedCatalog_getAvailableTypes' => $sharedCatalogAvailableTypes,
        ];

        $sharedCatalogId = 56;
        $this->sharedCatalog->expects($this->exactly(4))->method('getId')->willReturn($sharedCatalogId);

        $this->prepareValidateMethod($returned, $calls);

        $this->validator->validate($this->sharedCatalog);
    }

    /**
     * Test for validate() with NoSuchEntityException (SharedCatalogTaxClass validation).
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testValidateWithNoSuchEntityExceptionForTaxClass()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 1, 'sharedCatalogCollection_getFirstItem' => 1,
            'sharedCatalogCollectionFactory_create' => 1, 'sharedCatalog_getName' => 0,
            'sharedCatalog_getTaxClassId' => 2, 'storeRepository_getById' => 0,
            'sharedCatalog_getStoreId' => 0, 'sharedCatalog_getType' => 1, 'sharedCatalog_getAvailableTypes' => 1
        ];

        $taxClassId = 2634;
        $sharedCatalogType = 5;
        $sharedCatalogTaxClassId = 635;
        $sharedCatalogAvailableTypes = [$sharedCatalogType => $sharedCatalogType];
        $returned = [
            'sharedCatalog_getTaxClassId' => $sharedCatalogTaxClassId, 'taxClass_getId' => $taxClassId,
            'sharedCatalog_getType' => $sharedCatalogType,
            'sharedCatalog_getAvailableTypes' => $sharedCatalogAvailableTypes,
        ];

        $sharedCatalogId = 58;
        $this->sharedCatalog->expects($this->exactly(4))->method('getId')->willReturn($sharedCatalogId);

        $this->prepareValidateMethod($returned, $calls);

        $this->validator->validate($this->sharedCatalog);
    }

    /**
     * Test for validate() with InputException (SharedCatalogName validation).
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @return void
     */
    public function testValidateWithInputExceptionForSharedCatalogName()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 3, 'sharedCatalogCollection_getFirstItem' => 2,
            'sharedCatalogCollectionFactory_create' => 2, 'sharedCatalog_getName' => 3,
            'sharedCatalog_getTaxClassId' => 1, 'storeRepository_getById' => 1,
            'sharedCatalog_getStoreId' => 2, 'sharedCatalog_getType' => 1, 'sharedCatalog_getAvailableTypes' => 1
        ];

        $taxClassId = 2634;
        $sharedCatalogType = 5;
        $sharedCatalogAvailableTypes = [$sharedCatalogType => $sharedCatalogType];
        $returned = [
            'sharedCatalog_getTaxClassId' => $taxClassId, 'taxClass_getId' => $taxClassId,
            'sharedCatalog_getType' => $sharedCatalogType,
            'sharedCatalog_getAvailableTypes' => $sharedCatalogAvailableTypes,
        ];

        $sharedCatalogId = 59;
        $this->sharedCatalog->expects($this->exactly(7))->method('getId')->willReturn($sharedCatalogId);

        $this->prepareValidateMethod($returned, $calls);

        $this->validator->validate($this->sharedCatalog);
    }

    /**
     * Test for validate() with InputException (SharedCatalogName length validation).
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @return void
     */
    public function testValidateWithInputExceptionForSharedCatalogNameLength()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 1, 'sharedCatalogCollection_getFirstItem' => 1,
            'sharedCatalogCollectionFactory_create' => 1, 'sharedCatalog_getName' => 1,
            'sharedCatalog_getTaxClassId' => 1, 'storeRepository_getById' => 1,
            'sharedCatalog_getStoreId' => 2, 'sharedCatalog_getType' => 1, 'sharedCatalog_getAvailableTypes' => 1
        ];

        $taxClassId = 2634;
        $sharedCatalogType = 5;
        $sharedCatalogAvailableTypes = [$sharedCatalogType => $sharedCatalogType];
        $returned = [
            'sharedCatalog_getTaxClassId' => $taxClassId, 'taxClass_getId' => $taxClassId,
            'sharedCatalog_getType' => $sharedCatalogType,
            'sharedCatalog_getAvailableTypes' => $sharedCatalogAvailableTypes,
            'sharedCatalogName' => 'Very long name.........................................................'
        ];

        $sharedCatalogId = 1;
        $this->sharedCatalog->expects($this->any())->method('getId')->willReturn($sharedCatalogId);

        $this->prepareValidateMethod($returned, $calls);

        $this->validator->validate($this->sharedCatalog);
    }

    /**
     * Test for validate() with InputException (CustomerGroupChanges validation).
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @return void
     */
    public function testValidateWithInputExceptionForCustomerGroupChanges()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 4, 'sharedCatalogCollection_getFirstItem' => 3,
            'sharedCatalogCollectionFactory_create' => 3, 'sharedCatalog_getName' => 2,
            'sharedCatalog_getTaxClassId' => 1, 'storeRepository_getById' => 1,
            'sharedCatalog_getStoreId' => 2, 'sharedCatalog_getType' => 1, 'sharedCatalog_getAvailableTypes' => 1
        ];

        $taxClassId = 2634;
        $sharedCatalogType = 5;
        $sharedCatalogAvailableTypes = [$sharedCatalogType => $sharedCatalogType];
        $returned = [
            'sharedCatalog_getTaxClassId' => $taxClassId, 'taxClass_getId' => $taxClassId,
            'sharedCatalog_getType' => $sharedCatalogType,
            'sharedCatalog_getAvailableTypes' => $sharedCatalogAvailableTypes,
        ];

        $sharedCatalogId = 53;
        $this->sharedCatalog->expects($this->exactly(8))->method('getId')
            ->willReturnOnConsecutiveCalls(
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                $sharedCatalogId,
                null,
                $sharedCatalogId
            );
        $customerGroupId = 56;
        $this->sharedCatalog->expects($this->exactly(3))->method('getCustomerGroupId')
            ->willReturnOnConsecutiveCalls($customerGroupId, $customerGroupId, null);

        $this->prepareValidateMethod($returned, $calls);

        $this->validator->validate($this->sharedCatalog);
    }

    /**
     * Test for isCatalogPublicTypeDuplicated().
     *
     * @param bool $expects
     * @param array $returned
     * @param array $calls
     * @dataProvider isCatalogPublicTypeDuplicatedDataProvider
     * @return void
     */
    public function testIsCatalogPublicTypeDuplicated($expects, array $returned, array $calls)
    {
        $this->sharedCatalog->expects($this->once())->method('getType')
            ->willReturn($returned['sharedCatalog_getType']);

        $this->sharedCatalogManagement->expects($this->exactly($calls['sharedCatalogManagement_getPublicCatalog']))
            ->method('getPublicCatalog')
            ->willReturn($this->sharedCatalog);

        $sharedCatalogId = 5;
        $publicCatalogId = 34;
        $this->sharedCatalog->expects($this->exactly($calls['sharedCatalog_getId']))->method('getId')
            ->willReturnOnConsecutiveCalls($sharedCatalogId, $publicCatalogId);

        $result = $this->validator->isCatalogPublicTypeDuplicated($this->sharedCatalog);
        $this->assertEquals($expects, $result);
    }

    /**
     * Data provider for isCatalogPublicTypeDuplicated().
     *
     * @return array
     */
    public function isCatalogPublicTypeDuplicatedDataProvider()
    {
        $sharedCatalogPublicType =  \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC;
        return [
            [
                false, ['sharedCatalog_getType' => 9],
                ['sharedCatalogManagement_getPublicCatalog' => 0, 'sharedCatalog_getId' => 0]
            ],
            [
                true, ['sharedCatalog_getType' => $sharedCatalogPublicType],
                ['sharedCatalogManagement_getPublicCatalog' => 1, 'sharedCatalog_getId' => 2]
            ]
        ];
    }

    /**
     * Test for isCatalogPublicTypeDuplicated with NoSuchEntityException().
     *
     * @return void
     */
    public function testIsCatalogPublicTypeDuplicatedWithException()
    {
        $sharedCatalogPublicType =  \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogPublicType);

        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->sharedCatalogManagement->expects($this->once())
            ->method('getPublicCatalog')
            ->willThrowException($exception);

        $expects = false;
        $result = $this->validator->isCatalogPublicTypeDuplicated($this->sharedCatalog);
        $this->assertEquals($expects, $result);
    }

    /**
     * Test for isDirectChangeToCustom().
     *
     * @param bool $expects
     * @param array $returned
     * @param array $calls
     * @dataProvider isDirectChangeToCustomDataProvider
     * @return void
     */
    public function testIsDirectChangeToCustom($expects, array $returned, array $calls)
    {
        $this->sharedCatalog->expects($this->once())->method('getType')
            ->willReturn($returned['sharedCatalog_getType']);

        $this->sharedCatalogManagement->expects($this->exactly($calls['sharedCatalogManagement_getPublicCatalog']))
            ->method('getPublicCatalog')
            ->willReturn($this->sharedCatalog);

        $sharedCatalogId = 5;
        $publicCatalogId = 34;
        $this->sharedCatalog->expects($this->exactly($calls['sharedCatalog_getId']))->method('getId')
            ->willReturnOnConsecutiveCalls($sharedCatalogId, $publicCatalogId);

        $result = $this->validator->isDirectChangeToCustom($this->sharedCatalog);
        $this->assertEquals($expects, $result);
    }

    /**
     * Data provider for isDirectChangeToCustom().
     *
     * @return array
     */
    public function isDirectChangeToCustomDataProvider()
    {
        $sharedCatalogCustomType =  \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM;
        return [
            [
                false, ['sharedCatalog_getType' => 9],
                ['sharedCatalogManagement_getPublicCatalog' => 0, 'sharedCatalog_getId' => 0]
            ],
            [
                true, ['sharedCatalog_getType' => $sharedCatalogCustomType],
                ['sharedCatalogManagement_getPublicCatalog' => 1, 'sharedCatalog_getId' => 2]
            ]
        ];
    }

    /**
     * Test for isDirectChangeToCustom() with NoSuchEntityException.
     *
     * @return void
     */
    public function testIsDirectChangeToCustomWithNoSuchEntityException()
    {
        $sharedCatalogCustomType =  \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogCustomType);

        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->sharedCatalogManagement->expects($this->once())
            ->method('getPublicCatalog')
            ->willThrowException($exception);

        $expects = true;
        $result = $this->validator->isDirectChangeToCustom($this->sharedCatalog);
        $this->assertEquals($expects, $result);
    }

    /**
     * Test for isDirectChangeToCustom() with LocalizedException.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testIsDirectChangeToCustomWithLocalizedException()
    {
        $sharedCatalogCustomType =  \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogCustomType);

        $sharedCatalogId = 5;
        $this->sharedCatalog->expects($this->exactly(2))->method('getId')->willReturn($sharedCatalogId);

        $this->sharedCatalogManagement->expects($this->once())->method('getPublicCatalog')
            ->willReturn($this->sharedCatalog);

        $this->validator->isDirectChangeToCustom($this->sharedCatalog);
    }

    /**
     * Test checkSharedCatalogExist() with Exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested Shared Catalog is not found
     * @return void
     */
    public function testCheckSharedCatalogExistWithException()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 1, 'sharedCatalogCollection_getFirstItem' => 1,
            'sharedCatalogCollectionFactory_create' => 1
        ];

        $sharedCatalogId = 5;
        $this->sharedCatalog->expects($this->exactly(2))->method('getId')
            ->willReturnOnConsecutiveCalls($sharedCatalogId, null);

        $this->prepareSharedCatalogCollectionFactory($calls);

        $this->validator->checkSharedCatalogExist($this->sharedCatalog);
    }

    /**
     * Test for isSharedCatalogPublic().
     *
     * @return void
     */
    public function testIsSharedCatalogPublic()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 1, 'sharedCatalogCollection_getFirstItem' => 1,
            'sharedCatalogCollectionFactory_create' => 1
        ];

        $sharedCatalogId = 5;
        $this->sharedCatalog->expects($this->exactly(2))->method('getId')->willReturn($sharedCatalogId);

        $this->prepareSharedCatalogCollectionFactory($calls);

        $sharedCatalogCustomType =  \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_CUSTOM;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogCustomType);

        $expected = true;
        $result = $this->validator->isSharedCatalogPublic($this->sharedCatalog);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test for isSharedCatalogPublic() with LocalizedException.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testIsSharedCatalogPublicWithLocalizedException()
    {
        $calls = [
            'sharedCatalogCollection_addFieldToFilter' => 1, 'sharedCatalogCollection_getFirstItem' => 1,
            'sharedCatalogCollectionFactory_create' => 1
        ];

        $sharedCatalogId = 5;
        $this->sharedCatalog->expects($this->exactly(3))->method('getId')->willReturn($sharedCatalogId);

        $this->prepareSharedCatalogCollectionFactory($calls);

        $sharedCatalogPublicType =  \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC;
        $this->sharedCatalog->expects($this->once())->method('getType')->willReturn($sharedCatalogPublicType);

        $this->validator->isSharedCatalogPublic($this->sharedCatalog);
    }
}
