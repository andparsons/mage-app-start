<?php
namespace Magento\Setup\Model\Description;

/**
 * Generate random sentence for description based on configuration
 */
class DescriptionSentenceGenerator
{
    /**
     * @var \Magento\Setup\Model\Dictionary
     */
    private $dictionary;

    /**
     * @var array
     */
    private $sentenceConfig;

    /**
     * @param \Magento\Setup\Model\Dictionary $dictionary
     * @param array $sentenceConfig
     */
    public function __construct(
        \Magento\Setup\Model\Dictionary $dictionary,
        array $sentenceConfig
    ) {
        $this->dictionary = $dictionary;
        $this->sentenceConfig = $sentenceConfig;
    }

    /**
     * Generate sentence for description
     *
     * @return string
     */
    public function generate()
    {
        $sentenceWordsCount = random_int(
            $this->sentenceConfig['words']['count-min'],
            $this->sentenceConfig['words']['count-max']
        );
        $sentence = '';

        while ($sentenceWordsCount) {
            $sentence .= $this->dictionary->getRandWord();
            $sentence .= ' ';
            $sentenceWordsCount--;
        }

        return ucfirst(rtrim($sentence)) . '.';
    }
}
