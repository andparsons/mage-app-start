<?php
namespace Magento\Sales\Test\Unit\Model\Order\Email\Container;

use \Magento\Sales\Model\Order\Email\Container\Template;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Email\Container\Template
     */
    protected $template;

    protected function setUp()
    {
        $this->template = new Template();
    }

    public function testSetTemplateId()
    {
        $templateId = 'test_template_id';
        $this->template->setTemplateId($templateId);
        $result = $this->template->getTemplateId();
        $this->assertEquals($templateId, $result);
    }

    public function testSetTemplateOptions()
    {
        $templateOptions = ['opt1', 'opt2'];
        $this->template->setTemplateOptions($templateOptions);
        $result = $this->template->getTemplateOptions();
        $this->assertEquals($templateOptions, $result);
    }

    public function testSetTemplateVars()
    {
        $templateVars = ['opt1', 'opt2'];
        $this->template->setTemplateVars($templateVars);
        $result = $this->template->getTemplateVars();
        $this->assertEquals($templateVars, $result);
    }
}
