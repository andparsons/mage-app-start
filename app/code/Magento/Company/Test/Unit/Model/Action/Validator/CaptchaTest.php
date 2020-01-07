<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Unit\Model\Action\Validator;

/**
 * Class CaptchaTest.
 */
class CaptchaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Captcha\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Magento\Captcha\Observer\CaptchaStringResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stringResolver;

    /**
     * @var \Magento\Company\Model\Action\Validator\Captcha
     */
    private $captcha;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->helper = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->stringResolver = $this->createMock(
            \Magento\Captcha\Observer\CaptchaStringResolver::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->captcha = $objectManagerHelper->getObject(
            \Magento\Company\Model\Action\Validator\Captcha::class,
            [
                'helper' => $this->helper,
                'stringResolver' => $this->stringResolver,
            ]
        );
    }

    /**
     * Test for validate method.
     *
     * @return void
     */
    public function testValidate()
    {
        $formId = 1;
        $captchaValue = '123Q';
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $captcha = $this->getMockForAbstractClass(
            \Magento\Captcha\Model\CaptchaInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['isRequired', 'isCorrect']
        );
        $this->helper->expects($this->once())->method('getCaptcha')->with($formId)->willReturn($captcha);
        $captcha->expects($this->once())->method('isRequired')->willReturn(true);
        $this->stringResolver->expects($this->once())
            ->method('resolve')->with($request, $formId)->willReturn($captchaValue);
        $captcha->expects($this->once())->method('isCorrect')->with($captchaValue)->willReturn(true);

        $this->assertTrue($this->captcha->validate($formId, $request));
    }
}
