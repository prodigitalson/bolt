<?php

namespace Bolt\Extensions;

use Bolt\Asset\File\FileAssetInterface;
use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;

/**
 * Extension assets BC trait.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
trait AssetTrait
{
    /** @return \Silex\Application */
    abstract public function getApp();
    /** @return string */
    abstract public function getBaseUrl();
    /** @return string */
    abstract public function getBasePath();

    /**
     * Add a particular CSS file to the output. This will be inserted before the
     * other css files.
     *
     * @param FileAssetInterface|string $fileAsset Asset object, or file name
     */
    public function addCss($fileAsset, $options = [])
    {
        if (!$fileAsset instanceof FileAssetInterface) {
            $fileAsset = $this->setupAsset(new Stylesheet(), $fileAsset, func_get_args());
        }
        $this->getApp()['asset.queue.file']->add($fileAsset);
    }

    /**
     * Add a particular javascript file to the output. This will be inserted after
     * the other javascript files.
     *
     * @param FileAssetInterface|string $fileAsset File name
     */
    public function addJavascript($fileAsset, $options = [])
    {
        if (!$fileAsset instanceof FileAssetInterface) {
            $fileAsset = $this->setupAsset(new JavaScript(), $fileAsset, func_get_args());
        }
        $this->getApp()['asset.queue.file']->add($fileAsset);
    }

    /**
     * Get the relative path to the asset file.
     *
     * @param string $fileName
     *
     * @return string|null
     */
    private function getAssetPath($fileName)
    {
        if (file_exists($this->getBasePath() . '/' . $fileName)) {
            return $this->getBaseUrl() . $fileName;
        } elseif (file_exists($this->getApp()['resources']->getPath('themepath/' . $fileName))) {
            return $this->getApp()['resources']->getUrl('theme') . $fileName;
        } elseif ($this instanceof \Bolt\Extensions) {
            return $fileName;
        } else {
            $message = sprintf(
                "Couldn't add file asset '%s': File does not exist in either %s or %s directories.",
                $fileName,
                $this->getBaseUrl(),
                $this->getApp()['resources']->getUrl('theme')
            );
            $this->getApp()['logger.system']->error($message, ['event' => 'extensions']);
        }
    }

    /**
     * Set up an asset.
     *
     * @param FileAssetInterface $asset
     * @param string             $fileName
     * @param array              $options
     */
    private function setupAsset(FileAssetInterface $asset, $fileName, array $options)
    {
        $fileName = $this->getAssetPath($fileName);
        $options = array_merge(
            [
                'late'     => false,
                'priority' => 0,
                'attrib'   => null,
            ],
            $this->getCompatibleArgs($options)
        );

        $asset
            ->setFileName($fileName)
            ->setLate($options['late'])
            ->setPriority($options['priority'])
            ->setAttributes($options['attrib'])
        ;
        return $asset;
    }

    /**
     * Get options that are compatible with Bolt 2.1 & 2.2 function signatures.
     * < 2.2 ($filename, $late = false, $priority = 0)
     * 2.2.x ($filename, $options = [])
     *
     * Where options were:
     *   'late'     - True to add to the end of the HTML <body>
     *   'priority' - Loading priority
     *   'attrib'   - A string containing either/or 'defer', and 'async'
     *
     * Passed in $args array can be:
     * - args[0] always the file name
     * - args[1] either $late     or $options[]
     * - args[2] either $priority or not set
     *
     * @param array $args
     *
     * @return array
     */
    private function getCompatibleArgs(array $args)
    {
        if (!is_array($args[1])) {
            return [
                'late'     => isset($args[1]) ? $args[1] : false,
                'priority' => isset($args[2]) ? $args[2] : 0,
                'attrib'   => false
            ];
        }

        return $args[1];
    }
}
