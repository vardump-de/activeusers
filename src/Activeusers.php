<?php
/**
 * activeusers plugin for Craft CMS 3.x
 *
 * A widget showing active Users
 *
 * @link      https://vardump.de
 * @copyright Copyright (c) 2019 vardump.de
 */

namespace vardump\activeusers;

use vardump\activeusers\widgets\ActiveusersWidget as ActiveusersWidgetWidget;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Class Activeusers
 *
 * @author    vardump.de
 * @package   Activeusers
 * @since     1.0.0
 *
 */
class Activeusers extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Activeusers
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ActiveusersWidgetWidget::class;
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'activeusers',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
