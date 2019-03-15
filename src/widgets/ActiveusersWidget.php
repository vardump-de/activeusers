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

    /**
     * @var integer
     */
    public $inactive = 30;

    /**
     * @var integer
     */
    public $limit = 10;

    /**
     * @var integer
     */
    public $maxheight = 0;

    /**
     * @var string userlink
     */
    public $userlink = '/admin/users/{{user.id}}';

    /**
     * @var string userlink
     */
    public $linktarget = '';

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
                ['inactive', 'integer'],
                ['inactive', 'default', 'value' => 30],
                ['limit', 'integer'],
                ['limit', 'default', 'value' => 10],
                ['maxheight', 'integer'],
                ['maxheight', 'default', 'value' => 0],
                ['userlink', 'string'],
                ['userlink', 'default', 'value' => '/admin/users/{{user.id}}'],
                ['linktarget', 'string'],
                ['linktarget', 'default', 'value' => '']
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
                'widget' => $this,
                'sessionTimeout' => Craft::$app->getSession()->getTimeout()
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
        $interval = DateInterval::createFromDateString($timeout . ' seconds');
        $expire = DateTimeHelper::currentUTCDateTime();
        $pastTime = $expire->sub($interval);

        $userIds = (new Query())
            ->select('userid, dateUpdated')
            ->distinct()
            ->from([Table::SESSIONS])
            ->where(['and', ['>', 'dateUpdated', Db::prepareDateForDb($pastTime)],
                ['not', ['userid' => $currentUserId]]])
            ->orderBy("dateUpdated DESC")
            ->limit($this->limit)
            ->all();

        if (count($userIds)) {
            foreach ($userIds as $item) {
                $user = Craft::$app->getUsers()->getUserById($item['userid']);

                if ($user) {
                    $userData[] = array('user' => $user,
                        'dateUpdated' => DateTimeHelper::toDateTime($item['dateUpdated'])->getTimestamp(),
                        'link' =>  $this->parseUserLinkUrl( $this->userlink, array( 'user' => $user))
                    );
                }
            }
        }


        Craft::$app->getView()->registerAssetBundle(ActiveusersWidgetWidgetAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'activeusers/_components/widgets/ActiveusersWidget_body',
            [
                'userData' => $userData,
                'message' => $this->message,
                'inactive' => $this->inactive,
                'maxheight' => $this->maxheight,
                'sessionTimeout' => $timeout,
                'linktarget' => $this->linktarget,
                'now' => DateTimeHelper::currentUTCDateTime()->getTimestamp()
            ]
        );
    }

    /**
     * @param string $string
     * @param array $vars
     *
     * @return string
     */
    protected function parseUserLinkUrl($string, $vars = array())
    {
        $parsed = Craft::$app->view->renderString($string, $vars);
        return $parsed;
    }
}