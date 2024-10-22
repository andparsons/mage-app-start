<?php
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer;

use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;

/**
 * Value transformer for fields with text types.
 */
class TextTransformer implements ValueTransformerInterface
{
    /**
     * @var PreprocessorInterface[]
     */
    private $preprocessors;

    /**
     * @param PreprocessorInterface[] $preprocessors
     */
    public function __construct(array $preprocessors = [])
    {
        foreach ($preprocessors as $preprocessor) {
            if (!$preprocessor instanceof PreprocessorInterface) {
                throw new \InvalidArgumentException(
                    \sprintf('"%s" is not a instance of ValueTransformerInterface.', get_class($preprocessor))
                );
            }
        }

        $this->preprocessors = $preprocessors;
    }

    /**
     * @inheritdoc
     */
    public function transform(string $value): string
    {
        $value = $this->escape($value);
        foreach ($this->preprocessors as $preprocessor) {
            $value = $preprocessor->process($value);
        }

        return $value;
    }

    /**
     * Escape a value for special query characters such as ':', '(', ')', '*', '?', etc.
     *
     * @param string $value
     * @return string
     */
    private function escape(string $value): string
    {
        $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
