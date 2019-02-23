<?php
/**
 * activeusers plugin for Craft CMS 3.x
 *
 * A widget showing active Users
 *
 * @link      https://vardump.de
 * @copyright Copyright (c) 2019 vardump.de
 */

namespace vardump\activeusers\widgets;

use craft\db\Query;
use vardump\activeusers\Activeusers;
use vardump\activeusers\assetbundles\activeuserswidgetwidget\ActiveusersWidgetWidgetAsset;

use Craft;
use craft\base\Widget;

use craft\db\Table;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use DateInterval;


/**
 * activeusers Widget
 *
 * @author    vardump.de
 * @package   Activeusers
 * @since     1.0.0
 */
class ActiveusersWidget extends Widget
{

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $message = 'nobody out there :(';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('activeusers', 'ActiveusersWidget');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias("@vardump/activeusers/assetbundles/activeuserswidgetwidget/dist/img/ActiveusersWidget-icon.svg");
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan()
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            [
                ['message', 'string'],
                ['message', 'default', 'value' => 'nobody out there :('],
            ]
        );
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'activeusers/_components/widgets/ActiveusersWidget_settings',
            [
                'widget' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
        $userData = array();
        $currentUserId = Craft::$app->getUser()->getId();
        
        $timeout = Craft::$app->getSession()->getTimeout();
        $interval = DateInterval::createFromDateString($timeout.' seconds');
        $expire = DateTimeHelper::currentUTCDateTime();
        $pastTime = $expire->sub($interval);

        $userIds = (new Query())
            ->select('userid')
            ->distinct()
            ->from([Table::SESSIONS])
            ->where(['and', ['>', 'dateUpdated', Db::prepareDateForDb($pastTime)] ,
                            ['not', ['userid' => $currentUserId]] ])
            ->limit(10)
            ->column();

        if (count($userIds)) {
            foreach($userIds as $userId) {
                    $user = Craft::$app->getUsers()->getUserById($userId);
                    if ($user) {
                        $userData[] = $user;
                    }
            }
        }


        Craft::$app->getView()->registerAssetBundle(ActiveusersWidgetWidgetAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'activeusers/_components/widgets/ActiveusersWidget_body',
            [
                'userData' => $userData,
                'message' => $this->message
            ]
        );
    }
}
