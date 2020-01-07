<?php

namespace Magento\Company\Test\Unit\Controller\Adminhtml\Index;

/**
 * Class MassBlockTest.
 */
class MassBlockTest extends Mass
{
    /**
     * Action name.
     *
     * @var string
     */
    protected $actionName = 'Block';

    /**
     * Test execute.
     *
     * @return void
     */
    public function testExecute()
    {
        if (empty($this->actionName)) {
            return;
        }

        $companysIds = [10, 11, 12];

        $this->companyCollectionMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn($companysIds);

        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepositoryMock->expects($this->any())
            ->method('get')->will($this->returnValue($company));

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) were updated.', count($companysIds)));

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('company/*/index')
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Redirect::class, $this->massAction->execute());
    }

    /**
     * Test execute with Exception.
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception();
        $this->filterMock->expects($this->once())->method('getCollection')->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())->method('addException')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Redirect::class, $this->massAction->execute());
    }

    /**
     * Test execute with LocalizedException.
     */
    public function testExecuteWithLocalizedException()
    {
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->filterMock->expects($this->once())->method('getCollection')->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())->method('addError')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Redirect::class, $this->massAction->execute());
    }
}
