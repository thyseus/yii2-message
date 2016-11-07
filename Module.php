<?php
/**
 * This is the main module class for yii2-message.
 *
 * @property array $modelMap
 *
 * @author Herbert Maschke <thyseus@gmail.com>
 */

namespace thyseus\message;

use yii;
use yii\base\Module as BaseModule;
use yii\i18n\PhpMessageSource;

class Module extends BaseModule
{
    const VERSION = '0.1.0-dev';

    public $defaultRoute = 'message/message/inbox';

    /** @var array Model map */
    public $modelMap = [];

    /**
     * @var string The prefix for message module URL.
     *
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'message';

    /**
     * @var string The class of the User Model inside the application this module is attached to
     */
    public $userModelClass = 'app\models\User';

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        'message/inbox' => 'message/message/inbox',
        'message/sent' => 'message/message/sent',
        'message/compose/to/<to:\d+>/answers/<answers:\d+>' => 'message/message/compose',
        'message/compose/to/<to:\d+>' => 'message/message/compose',
        'message/compose/' => 'message/message/compose',
        'message/delete/<hash:\d+>' => 'message/message/delete',
        'message/<hash:\w+>' => 'message/message/view',
    ];

    public function init()
    {
        if (!isset(Yii::$app->get('i18n')->translations['message*'])) {
            Yii::$app->get('i18n')->translations['message*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US'
            ];
        }

        return parent::init();
    }
}
