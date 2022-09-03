<?php
/**
 * activeusers plugin for Craft CMS 4.x
 *
 * A widget showing active Users
 *
 * @link      https://vardump.de
 * @copyright Copyright (c) 2019-2022 vardump.de
 */

namespace vardump\activeusers;

use vardump\activeusers\widgets\ActiveusersWidget as ActiveusersWidgetWidget;

use Craft;
use craft\base\Plugin;
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
    public static Activeusers $plugin;

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

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

        Craft::info(
            Craft::t(
                'activeusers',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }
}
