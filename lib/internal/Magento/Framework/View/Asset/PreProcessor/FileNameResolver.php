<?php
namespace Magento\Framework\View\Asset\PreProcessor;

class FileNameResolver
{
    /**
     * @var AlternativeSource[]
     */
    private $alternativeSources;

    /**
     * FileNameResolver constructor.
     * @param array $alternativeSources
     * @internal param AlternativeSource $alternativeSource
     */
    public function __construct(array $alternativeSources = [])
    {
        $this->alternativeSources = array_map(
            function (AlternativeSourceInterface $alternativeSource) {
                return $alternativeSource;
            },
            $alternativeSources
        );
    }

    /**
     * Resolve filename
     *
     * @param string $fileName
     * @return string
     */
    public function resolve($fileName)
    {
        $compiledFile = $fileName;
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        foreach ($this->alternativeSources as $name => $alternative) {
            if (in_array($extension, $alternative->getAlternativesExtensionsNames(), true)
                && strpos(basename($fileName), '_') !== 0
            ) {
                $compiledFile = substr($fileName, 0, strlen($fileName) - strlen($extension) - 1);
                $compiledFile = $compiledFile . '.' . $name;
            }
        }
        return $compiledFile;
    }
}
