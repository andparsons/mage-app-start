<?php

namespace Magento\Framework;

/**
 * Magento escape methods
 *
 * @api
 * @since 100.0.2
 */
class Escaper
{
    /**
     * HTML special characters flag
     */
    private $htmlSpecialCharsFlag = ENT_QUOTES | ENT_SUBSTITUTE;

    /**
     * @var \Magento\Framework\ZendEscaper
     */
    private $escaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    private $translateInline;

    /**
     * @var string[]
     */
    private $notAllowedTags = ['script', 'img', 'embed', 'iframe', 'video', 'source', 'object', 'audio'];

    /**
     * @var string[]
     */
    private $allowedAttributes = ['id', 'class', 'href', 'target', 'title', 'style'];

    /**
     * @var string
     */
    private static $xssFiltrationPattern =
        '/((javascript(\\\\x3a|:|%3A))|(data(\\\\x3a|:|%3A))|(vbscript:))|'
        . '((\\\\x6A\\\\x61\\\\x76\\\\x61\\\\x73\\\\x63\\\\x72\\\\x69\\\\x70\\\\x74(\\\\x3a|:|%3A))|'
        . '(\\\\x64\\\\x61\\\\x74\\\\x61(\\\\x3a|:|%3A)))/i';

    /**
     * @var string[]
     */
    private $escapeAsUrlAttributes = ['href'];

    /**
     * Escape string for HTML context.
     *
     * AllowedTags will not be escaped, except the following: script, img, embed,
     * iframe, video, source, object, audio
     *
     * @param string|array $data
     * @param array|null $allowedTags
     * @return string|array
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (!is_array($data)) {
            $data = (string)$data;
        }

        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item, $allowedTags);
            }
        } elseif (!empty($data)) {
            if (is_array($allowedTags) && !empty($allowedTags)) {
                $allowedTags = $this->filterProhibitedTags($allowedTags);
                $wrapperElementId = uniqid();
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                set_error_handler(
                    function ($errorNumber, $errorString) {
                        // phpcs:ignore Magento2.Exceptions.DirectThrow
                        throw new \InvalidArgumentException($errorString, $errorNumber);
                    }
                );
                $data = $this->prepareUnescapedCharacters($data);
                $string = mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8');
                try {
                    $domDocument->loadHTML(
                        '<html><body id="' . $wrapperElementId . '">' . $string . '</body></html>'
                    );
                    // phpcs:disable Magento2.Exceptions.ThrowCatch
                } catch (\Exception $e) {
                    restore_error_handler();
                    $this->getLogger()->critical($e);
                }
                restore_error_handler();

                $this->removeComments($domDocument);
                $this->removeNotAllowedTags($domDocument, $allowedTags);
                $this->removeNotAllowedAttributes($domDocument);
                $this->escapeText($domDocument);
                $this->escapeAttributeValues($domDocument);

                $result = mb_convert_encoding($domDocument->saveHTML(), 'UTF-8', 'HTML-ENTITIES');
                preg_match('/<body id="' . $wrapperElementId . '">(.+)<\/body><\/html>$/si', $result, $matches);
                return !empty($matches) ? $matches[1] : '';
            } else {
                $result = htmlspecialchars($data, $this->htmlSpecialCharsFlag, 'UTF-8', false);
            }
        } else {
            $result = $data;
        }
        return $result;
    }

    /**
     * Used to replace characters, that mb_convert_encoding will not process
     *
     * @param string $data
     * @return string|null
     */
    private function prepareUnescapedCharacters(string $data): ?string
    {
        $patterns = ['/\&/u'];
        $replacements = ['&amp;'];
        return \preg_replace($patterns, $replacements, $data);
    }

    /**
     * Remove not allowed tags
     *
     * @param \DOMDocument $domDocument
     * @param string[] $allowedTags
     * @return void
     */
    private function removeNotAllowedTags(\DOMDocument $domDocument, array $allowedTags)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query(
            '//node()[name() != \''
            . implode('\' and name() != \'', array_merge($allowedTags, ['html', 'body']))
            . '\']'
        );
        foreach ($nodes as $node) {
            if ($node->nodeName != '#text') {
                $node->parentNode->replaceChild($domDocument->createTextNode($node->textContent), $node);
            }
        }
    }

    /**
     * Remove not allowed attributes
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function removeNotAllowedAttributes(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query(
            '//@*[name() != \'' . implode('\' and name() != \'', $this->allowedAttributes) . '\']'
        );
        foreach ($nodes as $node) {
            $node->parentNode->removeAttribute($node->nodeName);
        }
    }

    /**
     * Remove comments
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function removeComments(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//comment()');
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Escape text
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function escapeText(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $node->textContent = $this->escapeHtml($node->textContent);
        }
    }

    /**
     * Escape attribute values
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function escapeAttributeValues(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $value = $this->escapeAttributeValue(
                $node->nodeName,
                $node->parentNode->getAttribute($node->nodeName)
            );
            $node->parentNode->setAttribute($node->nodeName, $value);
        }
    }

    /**
     * Escape attribute value using escapeHtml or escapeUrl
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function escapeAttributeValue($name, $value)
    {
        return in_array($name, $this->escapeAsUrlAttributes) ? $this->escapeUrl($value) : $this->escapeHtml($value);
    }

    /**
     * Escape a string for the HTML attribute context
     *
     * @param string $string
     * @param boolean $escapeSingleQuote
     * @return string
     * @since 101.0.0
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        if ($escapeSingleQuote) {
            return $this->getEscaper()->escapeHtmlAttr((string) $string);
        }
        return htmlspecialchars((string)$string, $this->htmlSpecialCharsFlag, 'UTF-8', false);
    }

    /**
     * Escape URL
     *
     * @param string $string
     * @return string
     */
    public function escapeUrl($string)
    {
        return $this->escapeHtml($this->escapeXssInUrl($string));
    }

    /**
     * Encode URL
     *
     * @param string $string
     * @return string
     * @since 101.0.0
     */
    public function encodeUrlParam($string)
    {
        return $this->getEscaper()->escapeUrl($string);
    }

    /**
     * Escape string for the JavaScript context
     *
     * @param string $string
     * @return string
     * @since 101.0.0
     */
    public function escapeJs($string)
    {
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        return preg_replace_callback(
            '/[^a-z0-9,\._]/iSu',
            function ($matches) {
                $chr = $matches[0];
                if (strlen($chr) != 1) {
                    $chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                    $chr = ($chr === false) ? '' : $chr;
                }
                return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
            },
            $string
        );
    }

    /**
     * Escape string for the CSS context
     *
     * @param string $string
     * @return string
     * @since 101.0.0
     */
    public function escapeCss($string)
    {
        return $this->getEscaper()->escapeCss($string);
    }

    /**
     * Escape quotes in java script
     *
     * @param string|array $data
     * @param string $quote
     * @return string|array
     * @deprecated 101.0.0
     */
    public function escapeJsQuote($data, $quote = '\'')
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escapeJsQuote($item, $quote);
            }
        } else {
            $result = str_replace($quote, '\\' . $quote, (string)$data);
        }
        return $result;
    }

    /**
     * Escape xss in urls
     *
     * @param string $data
     * @return string
     * @deprecated 101.0.0
     */
    public function escapeXssInUrl($data)
    {
        $data = html_entity_decode((string)$data);
        $this->getTranslateInline()->processResponseBody($data);

        return htmlspecialchars(
            $this->escapeScriptIdentifiers($data),
            $this->htmlSpecialCharsFlag | ENT_HTML5 | ENT_HTML401,
            'UTF-8',
            false
        );
    }

    /**
     * Remove `javascript:`, `vbscript:`, `data:` words from the string.
     *
     * @param string $data
     * @return string
     */
    private function escapeScriptIdentifiers(string $data): string
    {
        $filteredData = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $data) ?: '';
        $filteredData = preg_replace(self::$xssFiltrationPattern, ':', $filteredData) ?: '';
        if (preg_match(self::$xssFiltrationPattern, $filteredData)) {
            $filteredData = $this->escapeScriptIdentifiers($filteredData);
        }

        return $filteredData;
    }

    /**
     * Escape quotes inside html attributes
     *
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     * @deprecated 101.0.0
     */
    public function escapeQuote($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, $this->htmlSpecialCharsFlag, null, false);
    }

    /**
     * Get escaper
     *
     * @return \Magento\Framework\ZendEscaper
     * @deprecated 101.0.0
     */
    private function getEscaper()
    {
        if ($this->escaper == null) {
            $this->escaper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\ZendEscaper::class);
        }
        return $this->escaper;
    }

    /**
     * Get logger
     *
     * @return \Psr\Log\LoggerInterface
     * @deprecated 101.0.0
     */
    private function getLogger()
    {
        if ($this->logger == null) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * Filter prohibited tags.
     *
     * @param string[] $allowedTags
     * @return string[]
     */
    private function filterProhibitedTags(array $allowedTags): array
    {
        $notAllowedTags = array_intersect(
            array_map('strtolower', $allowedTags),
            $this->notAllowedTags
        );

        if (!empty($notAllowedTags)) {
            $this->getLogger()->critical(
                'The following tag(s) are not allowed: ' . implode(', ', $notAllowedTags)
            );
            $allowedTags = array_diff($allowedTags, $this->notAllowedTags);
        }

        return $allowedTags;
    }

    /**
     * Resolve inline translator.
     *
     * @return \Magento\Framework\Translate\InlineInterface
     */
    private function getTranslateInline()
    {
        if ($this->translateInline === null) {
            $this->translateInline = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Translate\InlineInterface::class);
        }

        return $this->translateInline;
    }
}
