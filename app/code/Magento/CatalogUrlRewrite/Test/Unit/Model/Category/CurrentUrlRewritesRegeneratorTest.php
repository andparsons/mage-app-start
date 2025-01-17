<?php

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class CurrentUrlRewritesRegeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator */
    private $currentUrlRewritesRegenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    private $categoryUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject */
    private $category;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewriteFactory;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewrite;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $mergeDataProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $urlRewriteFinder;

    protected function setUp()
    {
        $this->urlRewriteFactory = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryUrlPathGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->urlRewriteFinder = $this->getMockBuilder(\Magento\CatalogUrlRewrite\Model\Map\UrlRewriteFinder::class)
            ->disableOriginalConstructor()->getMock();
        $this->urlRewriteFactory->expects($this->once())->method('create')
            ->willReturn($this->urlRewrite);
        $mergeDataProviderFactory = $this->createPartialMock(
            \Magento\UrlRewrite\Model\MergeDataProviderFactory::class,
            ['create']
        );
        $this->mergeDataProvider = new \Magento\UrlRewrite\Model\MergeDataProvider;
        $mergeDataProviderFactory->expects($this->once())->method('create')->willReturn($this->mergeDataProvider);

        $this->currentUrlRewritesRegenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'mergeDataProviderFactory' => $mergeDataProviderFactory,
                'urlRewriteFinder' => $this->urlRewriteFinder
            ]
        );
    }

    public function testIsAutogeneratedWithoutSaveRewriteHistory()
    {
        $this->urlRewriteFinder->expects($this->once())->method('findAllByData')
            ->will($this->returnValue($this->getCurrentRewritesMocks([[UrlRewrite::IS_AUTOGENERATED => 1]])));
        $this->category->expects($this->once())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue(false));

        $this->assertEquals(
            [],
            $this->currentUrlRewritesRegenerator->generate('store_id', $this->category, $this->category)
        );
    }

    public function testSkipGenerationForAutogenerated()
    {
        $this->urlRewriteFinder->expects($this->once())->method('findAllByData')
            ->will(
                $this->returnValue(
                    $this->getCurrentRewritesMocks(
                        [
                            [UrlRewrite::IS_AUTOGENERATED => 1, UrlRewrite::REQUEST_PATH => 'same-path'],
                        ]
                    )
                )
            );
        $this->category->expects($this->once())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue(true));
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPathWithSuffix')
            ->will($this->returnValue('same-path'));

        $this->assertEquals(
            [],
            $this->currentUrlRewritesRegenerator->generate('store_id', $this->category, $this->category)
        );
    }

    public function testIsAutogenerated()
    {
        $requestPath = 'autogenerated.html';
        $targetPath = 'some-path.html';
        $storeId = 2;
        $categoryId = 12;
        $this->urlRewriteFinder->expects($this->once())->method('findAllByData')
            ->will(
                $this->returnValue(
                    $this->getCurrentRewritesMocks(
                        [
                            [
                                UrlRewrite::REQUEST_PATH => $requestPath,
                                UrlRewrite::TARGET_PATH => 'custom-target-path',
                                UrlRewrite::STORE_ID => $storeId,
                                UrlRewrite::IS_AUTOGENERATED => 1,
                                UrlRewrite::METADATA => [],
                            ],
                        ]
                    )
                )
            );

        $this->category->expects($this->any())->method('getEntityId')->will($this->returnValue($categoryId));
        $this->category->expects($this->once())->method('getData')->with('save_rewrites_history')
            ->will($this->returnValue(true));
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($targetPath));

        $this->prepareUrlRewriteMock($storeId, $categoryId, $requestPath, $targetPath, OptionProvider::PERMANENT, 0);

        $this->assertEquals(
            ['autogenerated.html_2' => $this->urlRewrite],
            $this->currentUrlRewritesRegenerator->generate($storeId, $this->category, $this->category)
        );
    }

    public function testSkipGenerationForCustom()
    {
        $this->urlRewriteFinder->expects($this->once())->method('findAllByData')
            ->will(
                $this->returnValue(
                    $this->getCurrentRewritesMocks(
                        [
                            [
                                UrlRewrite::IS_AUTOGENERATED => 0,
                                UrlRewrite::REQUEST_PATH => 'same-path',
                                UrlRewrite::REDIRECT_TYPE => 1,
                            ],
                        ]
                    )
                )
            );
        $this->categoryUrlPathGenerator->expects($this->once())->method('getUrlPathWithSuffix')
            ->will($this->returnValue('same-path'));

        $this->assertEquals(
            [],
            $this->currentUrlRewritesRegenerator->generate('store_id', $this->category, $this->category)
        );
    }

    public function testGenerationForCustomWithoutTargetPathGeneration()
    {
        $storeId = 12;
        $categoryId = 123;
        $requestPath = 'generate-for-custom-without-redirect-type.html';
        $targetPath = 'custom-target-path.html';
        $description = 'description';
        $this->urlRewriteFinder->expects($this->once())->method('findAllByData')
            ->will(
                $this->returnValue(
                    $this->getCurrentRewritesMocks(
                        [
                            [
                                UrlRewrite::REQUEST_PATH => $requestPath,
                                UrlRewrite::TARGET_PATH => $targetPath,
                                UrlRewrite::REDIRECT_TYPE => 0,
                                UrlRewrite::IS_AUTOGENERATED => 0,
                                UrlRewrite::DESCRIPTION => $description,
                                UrlRewrite::METADATA => [],
                            ],
                        ]
                    )
                )
            );
        $this->categoryUrlPathGenerator->expects($this->never())->method('getUrlPathWithSuffix');
        $this->category->expects($this->any())->method('getEntityId')->will($this->returnValue($categoryId));
        $this->urlRewrite->expects($this->once())->method('setDescription')->with($description)
            ->will($this->returnSelf());
        $this->prepareUrlRewriteMock($storeId, $categoryId, $requestPath, $targetPath, 0, 0);

        $this->assertEquals(
            ['generate-for-custom-without-redirect-type.html_12' => $this->urlRewrite],
            $this->currentUrlRewritesRegenerator->generate($storeId, $this->category, $this->category)
        );
    }

    public function testGenerationForCustomWithTargetPathGeneration()
    {
        $storeId = 12;
        $categoryId = 123;
        $requestPath = 'generate-for-custom-without-redirect-type.html';
        $targetPath = 'generated-target-path.html';
        $description = 'description';
        $this->urlRewriteFinder->expects($this->once())->method('findAllByData')
            ->will(
                $this->returnValue(
                    $this->getCurrentRewritesMocks(
                        [
                            [
                                UrlRewrite::REQUEST_PATH => $requestPath,
                                UrlRewrite::TARGET_PATH => 'custom-target-path.html',
                                UrlRewrite::REDIRECT_TYPE => 'code',
                                UrlRewrite::IS_AUTOGENERATED => 0,
                                UrlRewrite::DESCRIPTION => $description,
                                UrlRewrite::METADATA => [],
                            ],
                        ]
                    )
                )
            );
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($targetPath));
        $this->category->expects($this->any())->method('getEntityId')->will($this->returnValue($categoryId));
        $this->urlRewrite->expects($this->once())->method('setDescription')->with($description)
            ->will($this->returnSelf());
        $this->prepareUrlRewriteMock($storeId, $categoryId, $requestPath, $targetPath, 'code', 0);

        $this->assertEquals(
            ['generate-for-custom-without-redirect-type.html_12' => $this->urlRewrite],
            $this->currentUrlRewritesRegenerator->generate($storeId, $this->category, $this->category)
        );
    }

    /**
     * @param array $currentRewrites
     * @return array
     */
    protected function getCurrentRewritesMocks($currentRewrites)
    {
        $rewrites = [];
        foreach ($currentRewrites as $urlRewrite) {
            /** @var \PHPUnit_Framework_MockObject_MockObject */
            $url = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
                ->disableOriginalConstructor()->getMock();
            foreach ($urlRewrite as $key => $value) {
                $url->expects($this->any())
                    ->method('get' . str_replace('_', '', ucwords($key, '_')))
                    ->will($this->returnValue($value));
            }
            $rewrites[] = $url;
        }
        return $rewrites;
    }

    /**
     * @param mixed $storeId
     * @param mixed $categoryId
     * @param mixed $requestPath
     * @param mixed $targetPath
     * @param mixed $redirectType
     * @param int $isAutoGenerated
     */
    protected function prepareUrlRewriteMock(
        $storeId,
        $categoryId,
        $requestPath,
        $targetPath,
        $redirectType,
        $isAutoGenerated
    ) {
        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($categoryId)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(CategoryUrlRewriteGenerator::ENTITY_TYPE)->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($requestPath)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($targetPath)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setIsAutogenerated')->with($isAutoGenerated)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setRedirectType')->with($redirectType)
            ->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('setMetadata')->with([])->will($this->returnSelf());
        $this->urlRewrite->expects($this->any())->method('getTargetPath')->willReturn($targetPath);
        $this->urlRewrite->expects($this->any())->method('getRequestPath')->willReturn($requestPath);
        $this->urlRewrite->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->urlRewriteFactory->expects($this->any())->method('create')->will($this->returnValue($this->urlRewrite));
    }
}
