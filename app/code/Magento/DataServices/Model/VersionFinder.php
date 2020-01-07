<?php
declare(strict_types=1);

namespace Magento\DataServices\Model;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Model for getting extension versions from filesystem
 */
class VersionFinder implements VersionFinderInterface
{
    /**
     * @var ComposerInformation
     */
    private $composerInfo;

    /**
     * @var Files
     */
    private $files;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param ComposerInformation $composerInfo
     * @param Files $files
     * @param Json $jsonSerializer
     */
    public function __construct(
        ComposerInformation $composerInfo,
        Files $files,
        Json $jsonSerializer
    ) {
        $this->composerInfo = $composerInfo;
        $this->files = $files;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @inheritDoc
     */
    public function getVersionFromComposer(string $packageName)
    {
        $catalogVersion = null;
        $packages = $this->composerInfo->getInstalledMagentoPackages();
        if (!empty($packages[$packageName]['version'])) {
            $catalogVersion = $packages[$packageName]['version'];
        }
        return $catalogVersion;
    }

    /**
     * @inheritDoc
     */
    public function getVersionFromFiles(string $moduleName, string $packageName)
    {
        $pathToModule = null;
        $composerFilePaths = array_keys($this->files->getComposerFiles(ComponentRegistrar::MODULE));
        $version = null;

        foreach ($composerFilePaths as $path) {
            if (strpos($path, $packageName . '/composer.json')) {
                $pathToModule = $path;
                break;
            }
            if (strpos($path, $moduleName . '/composer.json')) {
                $pathToModule = $path;
                break;
            }
        }

        if ($pathToModule) {
            $content = file_get_contents(BP . '/' . $pathToModule);
            if ($content) {
                $json = $this->jsonSerializer->unserialize($content);
                if (!empty($json['version'])) {
                    $version = $json['version'];
                }
            }
        }
        return $version;
    }
}
