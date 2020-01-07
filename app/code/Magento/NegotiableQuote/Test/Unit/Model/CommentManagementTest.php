<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\CollectionFactory as CommentAttachmentCollection;
use Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory as CommentCollection;

/**
 * Test for Magento\NegotiableQuote\Model\CommentManagement class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentFactory;

    /**
     * @var CommentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var CommentAttachmentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attachmentCollectionFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\Attachment\UploadHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploadHandler;

    /**
     * @var \Magento\NegotiableQuote\Model\CommentManagement
     */
    private $commentManagement;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerNameGeneration;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->commentFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\CommentInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerNameGeneration = $this->getMockBuilder(
            \Magento\Customer\Api\CustomerNameGenerationInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->provider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Purged\Provider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attachmentCollectionFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploadHandler = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Attachment\UploadHandler::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->uploaderFactory = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Attachment\UploadHandlerFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext = $this->getMockBuilder(
            \Magento\Authorization\Model\UserContextInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->commentManagement = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\CommentManagement::class,
            [
                'commentFactory' => $this->commentFactory,
                'collectionFactory' => $this->collectionFactory,
                'attachmentCollectionFactory' => $this->attachmentCollectionFactory,
                'uploadHandlerFactory' => $this->uploaderFactory,
                'provider' => $this->provider,
                'customerNameGeneration' => $this->customerNameGeneration,
                'customerRepository' => $this->customerRepository,
                'escaper' => $this->escaper,
                'userContext' => $this->userContext,
            ]
        );
    }

    /**
     * Test update method without quote id.
     *
     * @return void
     */
    public function testUpdateWithoutQuoteId()
    {
        $quoteId = 0;
        $this->assertEquals(false, $this->commentManagement->update($quoteId, '', []));
    }

    /**
     * Test update method with empty comment text and without attachment.
     *
     * @return void
     */
    public function testUpdateWithEmptyCommentTextAndWithoutAttachments()
    {
        $quoteId = 1;
        $commentId = 1;
        $comment = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\CommentInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'setEntityId'])
            ->getMockForAbstractClass();
        $collection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItem = $this->getMockBuilder(
            \Magento\Framework\DataObject::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMock();
        $attachmentCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'setOrder', 'getItems'])
            ->getMock();
        $this->attachmentCollectionFactory->expects($this->once())->method('create')->willReturn($attachmentCollection);
        $attachmentCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('comment_id', $commentId)
            ->willReturnSelf();
        $attachmentCollection->expects($this->once())
            ->method('setOrder')
            ->with('file_name', 'ASC')
            ->willReturnSelf();
        $this->collectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($collection);
        $collection->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->withConsecutive(['parent_id', $quoteId], ['is_draft', ['eq' => true]])
            ->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getFirstItem')->willReturn($collectionItem);
        $this->commentFactory->expects($this->once())->method('create')->willReturn($comment);
        $collectionItem->expects($this->once())->method('getEntityId')->willReturn(1);
        $comment->expects($this->once())->method('setEntityId')->with(1)->willReturnSelf();
        $comment->expects($this->once())->method('delete');

        $this->assertEquals(true, $this->commentManagement->update($quoteId, '', []));
    }

    /**
     * Test update.
     *
     * @return void
     */
    public function testUpdate()
    {
        $quoteId = 1;
        $commentText = 'Comment Text';
        $attachment = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creatorId = 2;
        $comment = $this->getMockBuilder(
            \Magento\NegotiableQuote\Api\Data\CommentInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setEntityId',
                    'setCreatorId',
                    'setParentId',
                    'setCreatorType',
                    'setIsDecline',
                    'setIsDraft',
                    'setComment',
                    'save',
                    'getId'
                ]
            )
            ->getMockForAbstractClass();
        $collection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItem = $this->getMockBuilder(
            \Magento\Framework\DataObject::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId'])
            ->getMock();
        $this->collectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($collection);
        $collection->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->withConsecutive(['parent_id', $quoteId], ['is_draft', ['eq' => true]])
            ->willReturnSelf();
        $collection->expects($this->atLeastOnce())->method('getFirstItem')->willReturn($collectionItem);
        $this->commentFactory->expects($this->once())->method('create')->willReturn($comment);
        $collectionItem->expects($this->once())->method('getEntityId')->willReturn(1);
        $this->escaper->expects($this->once())->method('escapeHtml')->with($commentText)->willReturn($commentText);
        $comment->expects($this->once())->method('setEntityId')->with(1)->willReturnSelf();
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($creatorId);
        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $comment->expects($this->once())->method('setCreatorId')->with($creatorId)->willReturnSelf();
        $comment->expects($this->once())->method('setParentId')->with($quoteId)->willReturnSelf();
        $comment->expects($this->once())
            ->method('setCreatorType')
            ->with(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER)
            ->willReturnSelf();
        $comment->expects($this->once())->method('setIsDecline')->with(false)->willReturnSelf();
        $comment->expects($this->once())->method('setIsDraft')->with(false)->willReturnSelf();
        $comment->expects($this->once())->method('setComment')->with($commentText)->willReturnSelf();
        $comment->expects($this->once())->method('save')->willReturnSelf();
        $comment->expects($this->once())->method('getId')->willReturn(1);
        $this->uploaderFactory->expects($this->once())
            ->method('create')
            ->with(['commentId' => 1])
            ->willReturn($this->uploadHandler);
        $this->uploadHandler->expects($this->atLeastOnce())->method('process');

        $this->assertTrue($this->commentManagement->update($quoteId, $commentText, [$attachment]));
    }

    /**
     * Data provider for getFilesNamesList method.
     *
     * @return array
     */
    public function getFilesNamesListDataProvider()
    {
        return [
            [
                [
                    'test_1' => [
                        'tmp_name' => 'name_1', //tmp_name
                        'size' => '10', //size
                    ],
                    'test_2' => [
                        'tmp_name' => 'name_2', //tmp_name
                        'size' => '20', //size
                    ],
                    'test_3' => [
                        'tmp_name' => 'name_2', //tmp_name
                        'size' => '30', //size
                    ]
                ],
                [
                    'files[test_1]',
                    'files[test_2]',
                    'files[test_3]'
                ],
            ],
            [
                [
                    'test_1' => [
                        'tmp_name' => 'name_1', //tmp_name
                        'size' => '10', //size
                    ],
                    'test_2' => [
                        'tmp_name' => 'name_2', //tmp_name
                        'size' => '', //size
                    ],
                    'test_3' => [
                        'tmp_name' => '', //tmp_name
                        'size' => '20', //size
                    ]
                ],
                [
                    'files[test_1]'
                ],
            ]
        ];
    }

    /**
     * Test for method getQuoteComments.
     *
     * @return void
     */
    public function testGetQuoteComments()
    {
        $quoteId = 14;
        $collection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($collection);
        $collection->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->withConsecutive(['parent_id', $quoteId], ['is_draft', ['eq' => true]])
            ->willReturnSelf();

        $this->assertEquals($collection, $this->commentManagement->getQuoteComments($quoteId, true));
    }

    /**
     * Test for method getCommentAttachments.
     *
     * @return void
     */
    public function testGetCommentAttachments()
    {
        $commentId = 12;
        $attachmentCollection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'setOrder'])
            ->getMock();
        $this->attachmentCollectionFactory->expects($this->once())->method('create')->willReturn($attachmentCollection);
        $attachmentCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('comment_id', $commentId)
            ->willReturnSelf();
        $attachmentCollection->expects($this->once())
            ->method('setOrder')
            ->with('file_name', 'ASC')
            ->willReturnSelf();

        $this->assertEquals(
            $attachmentCollection,
            $this->commentManagement->getCommentAttachments($commentId)
        );
    }

    /**
     * Test for method getCreatorName().
     *
     * @dataProvider getCreatorNameDataProvider
     *
     * @param int $id
     * @param string $result
     * @return void
     */
    public function testGetCreatorName($id, $result)
    {
        $quoteId = 436;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository->expects($this->once())->method('getById')->with($id)->willReturn($customer);
        $this->customerNameGeneration->expects($this->once())
            ->method('getCustomerName')
            ->with($customer)
            ->willReturn($result);

        $this->assertEquals(
            $result,
            $this->commentManagement->getCreatorName($id, $quoteId, false)
        );
    }

    /**
     * Data provider for getCreatorName method.
     *
     * @return array
     */
    public function getCreatorNameDataProvider()
    {
        return [
            [
                '1', //customerId
                'prefix first_name middle_name last_name suffix', //result name
            ],
            [
                '2', //customerId
                'first_name middle_name last_name suffix', //result name
            ],
            [
                '3', //customerId
                'prefix first_name last_name suffix', //result name
            ],
            [
                '4', //customerId
                'prefix first_name middle_name last_name', //result name
            ],
            [
                '5', //customerId
                'first_name last_name suffix', //result name
            ],
            [
                '6', //customerId
                'prefix first_name last_name', //result name
            ],
            [
                '7', //customerId
                'first_name middle_name last_name', //result name
            ],
        ];
    }

    /**
     * Test for method getCreatorName() when Customer is Seller.
     *
     * @return void
     */
    public function testGetCreatorNameWhenCustomerIsSeller()
    {
        $quoteId = 436;
        $creatorId = 34;
        $providerSalesRepName = 'Test Author';
        $this->provider->expects($this->once())
            ->method('getSalesRepresentativeName')->with($quoteId)->willReturn($providerSalesRepName);

        $expected = 'Test Author';
        $this->assertEquals(
            $expected,
            $this->commentManagement->getCreatorName($creatorId, $quoteId, true)
        );
    }

    /**
     * Test for method getCreatorName() with Exception.
     *
     * @return void
     */
    public function testGetCreatorNameWithException()
    {
        $creatorId = 25;
        $quoteId = 23;
        $creatorName = 'Test Author';

        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->customerRepository->expects($this->once())->method('getById')->willThrowException($exception);

        $this->provider->expects($this->once())->method('getCustomerName')->with($quoteId)->willReturn($creatorName);

        $this->assertEquals($creatorName, $this->commentManagement->getCreatorName($creatorId, $quoteId, false));
    }

    /**
     * Test for method getFilesNamesList().
     *
     * @dataProvider getFilesNamesListDataProvider
     *
     * @param array $fileNames
     * @param array $result
     * @return void
     */
    public function testGetFilesNamesList(array $fileNames, array $result)
    {
        $this->assertEquals(
            $result,
            $this->commentManagement->getFilesNamesList($fileNames)
        );
    }

    /**
     * Test for method hasDraftComment.
     *
     * @return void
     */
    public function testHasDraftComment()
    {
        $quoteId = 1;
        $collection = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItem = $this->getMockBuilder(
            \Magento\Framework\DataObject::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactory->expects($this->atLeastOnce())->method('create')->willReturn($collection);
        $collection->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->withConsecutive(['parent_id', $quoteId], ['is_draft', ['eq' => true]])
            ->willReturnSelf();
        $collection->expects($this->once())->method('getFirstItem')->willReturn($collectionItem);

        $this->assertEquals(
            true,
            $this->commentManagement->hasDraftComment($quoteId)
        );
    }

    /**
     * Test existsLogAuthor method.
     *
     * @param int $companyStatus
     * @param bool $result
     * @dataProvider existsLogAuthorDataProvider
     * @return void
     */
    public function testExistsLogAuthor($companyStatus, $result)
    {
        $creatorId = 23;

        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->setMethods([])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $companyAttributes->expects($this->once())->method('getStatus')->willReturn($companyStatus);

        $extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $extensionAttributes->expects($this->exactly(2))->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['getExtensionAttributes'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $customer->expects($this->exactly(3))->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);

        $this->assertEquals($result, $this->commentManagement->checkCreatorLogExists($creatorId));
    }

    /**
     * Data provider existsLogAuthor method.
     *
     * @return array
     */
    public function existsLogAuthorDataProvider()
    {
        return [
            [\Magento\Company\Api\Data\CompanyInterface::STATUS_REJECTED, false],
            [\Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED, true]
        ];
    }

    /**
     * Test existsLogAuthor method with Exception.
     *
     * @return void
     */
    public function testExistsLogAuthorWithException()
    {
        $creatorId = 23;

        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->customerRepository->expects($this->once())->method('getById')->willThrowException($exception);

        $this->assertEquals(false, $this->commentManagement->checkCreatorLogExists($creatorId));
    }
}
