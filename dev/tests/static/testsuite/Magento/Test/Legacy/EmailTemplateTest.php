<?php

/**
 * Tests for obsolete directives in email templates
 */
namespace Magento\Test\Legacy;

class EmailTemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testObsoleteDirectives()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $file
             */
            function ($file) {
                $this->assertNotRegExp(
                    '/\{\{htmlescape.*?\}\}/i',
                    file_get_contents($file),
                    'Directive {{htmlescape}} is obsolete. Use {{var}} instead.'
                );

                $this->assertNotRegExp(
                    '/\{\{escapehtml.*?\}\}/i',
                    file_get_contents($file),
                    'Directive {{escapehtml}} is obsolete. Use {{var}} instead.'
                );
            },
            \Magento\Framework\App\Utility\Files::init()->getEmailTemplates()
        );
    }
}
