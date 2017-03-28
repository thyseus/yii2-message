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
    const VERSION = '0.3.0-dev';

    public $defaultRoute = 'message/message/inbox';

    /** @var array Model map */
    public $modelMap = [];

    /**
     * @var string The prefix for message module
     *
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'message';

    /**
     * @var string Should the message be sent to the recipient by email?
     * The user model should have an attribute 'email'. Can be a callback so the recipient can decide
     * if he wants to receive messages e.g.
     * 'mailMessages' => function($recipient) { return $recipient->profile->i_want_to_receive_messages_by_email; }
     */
    public $mailMessages = true;

    /**
     * @var string A string that should be prefixed to the title when answering an message.
     * Defaults to 'Re: ' and can be adjusted for foreign languages, for example 'Aw: ' in german.
     */
    public $answerPrefix = 'Re: ';

    /**
     * @var string Callback that defines which users are not possible to write messages to.
     * Use this if you have restrictions about which user is able to write to whom.
     *
     * For example, to avoid to be able to write message to user id 3, 4 and 5 you could use:
     *
     * 'recipientsFilterCallback' => function ($users) {
     *    return array_filter($users, function ($user) {
     *      return !in_array($user->id, [3, 4, 5]); // or !$user->isAdmin()
     *    });
     *  },
     *
     */
    public $recipientsFilterCallback = null;

    /**
     * @var string The class of the User Model inside the application this module is attached to
     */
    public $userModelClass = 'app\models\User';

    /**
     * @var int The number of seconds that needs to pass until the user gets a reminder that he has
     * new messages.
     */
    public $newMessagesEverySeconds = 3600;

    /**
     * @var string The route that should be used to generate links towards an user.
     * Could be ['//user/profile/view'] for example. The id gets appended automatically.
     */
    public $userProfileRoute = null;

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        'message/inbox' => 'message/message/inbox',
        'message/ignorelist' => 'message/message/ignorelist',
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
