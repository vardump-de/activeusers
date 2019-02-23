<?php
/**
 * activeusers plugin for Craft CMS 3.x
 *
 * A widget showing active Users
 *
 * @link      https://vardump.de
 * @copyright Copyright (c) 2019 vardump.de
 */

namespace vardump\activeusers\assetbundles\activeuserswidgetwidget;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    vardump.de
 * @package   Activeusers
 * @since     1.0.0
 */
class ActiveusersWidgetWidgetAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@vardump/activeusers/assetbundles/activeuserswidgetwidget/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ActiveusersWidget.js',
        ];

        $this->css = [
            'css/ActiveusersWidget.css',
        ];

        parent::init();
    }
}
