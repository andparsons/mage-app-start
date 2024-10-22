<?php
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Category\Image;

use Magento\Catalog\Controller\Adminhtml\Category\Image\Upload as Model;
use Magento\Framework\App\Request\Http as Request;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;

/**
 * Class UploadTest
 */
class UploadTest extends \PHPUnit\Framework\TestCase
{
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['image1', 'image1'],
            ['image2', 'image2'],
            [null, 'image'],
        ];
    }

    /**
     * @param string $name
     * @param string $savedName
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($name, $savedName)
    {
        $request = $this->objectManager->getObject(Request::class);

        $uploader = $this->createPartialMock(ImageUploader::class, ['saveFileToTmpDir']);

        $resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);

        $resultFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new DataObject()));

        $model = $this->objectManager->getObject(Model::class, [
            'request' => $request,
            'resultFactory' => $resultFactory,
            'imageUploader' => $uploader
        ]);

        $uploader->expects($this->once())
            ->method('saveFileToTmpDir')
            ->with($savedName)
            ->will($this->returnValue([]));

        $request->setParam('param_name', $name);

        $model->execute();
    }
}
