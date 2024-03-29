<?php
/**
 * activeusers plugin for Craft CMS 4.x
 *
 * A widget showing active Users
 *
 * @link      https://vardump.de
 * @copyright Copyright (c) 2019-2022 vardump.de
 */

namespace vardump\activeusers\widgets;

use craft\db\Query;
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

    public string $message = 'nobody out there :(';

    public int $inactive = 30;

    public int $limit = 10;

    public int $maxheight = 0;

    public string $userlink = '/admin/users/{{user.id}}';

    public string $linktarget = '';

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
    public static function icon(): ?string
    {
        return Craft::getAlias("@vardump/activeusers/assetbundles/activeuserswidgetwidget/dist/img/ActiveusersWidget-icon.svg");
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan(): ?int
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        return array_merge(
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
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
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
    public function getBodyHtml(): ?string
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

    protected function parseUserLinkUrl(string $string, array $vars = array()): string
    {
        return Craft::$app->view->renderString($string, $vars);
    }
}