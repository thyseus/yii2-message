<?php

/**
 * This is the model class for yii2-message.
 *
 */

namespace thyseus\message\models;

use thyseus\message\jobs\EmailJob;
use thyseus\message\validators\IgnoreListValidator;
use yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class Message extends ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_UNREAD = 0;
    const STATUS_READ = 1;
    const STATUS_ANSWERED = 2;
    const STATUS_DRAFT = 3;
    const STATUS_TEMPLATE = 4;
    const STATUS_SIGNATURE = 5;

    const EVENT_BEFORE_MAIL = 'before_mail';
    const EVENT_AFTER_MAIL = 'after_mail';

    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * @param $from the user id of the sender. Set to null to send a 'system' message.
     * @param $to the user id of the recipient
     * @param $title title of the message (required)
     * @param string $message body of the message (optional)
     * @param null $context set a string or url to define what this message referrs to (optional)
     * @return Message
     */
    public static function compose($from, $to, $title, $message = '', $context = null)
    {
        $model = new Message;
        $model->from = $from;
        $model->to = $to;
        $model->title = $title;
        $model->message = $message;
        $model->context = $context;
        $model->status = self::STATUS_UNREAD;
        $model->save();
        return $model;
    }

    public static function isUserIgnoredBy($victim, $offender)
    {
        foreach (Message::getIgnoredUsers($victim) as $ignored_user) {
            if ($offender == $ignored_user->blocks_user_id) {
                return true;
            }
        }

        return false;
    }

    public static function getIgnoredUsers($for_user)
    {
        return IgnoreListEntry::find()->where(['user_id' => $for_user])->all();
    }

    /**
     * returns an array of possible recipients for the given user. Applies the ignorelist and applies possible custom
     * logic.
     * @param $for_user
     * @return mixed
     */
    public static function getPossibleRecipients($for_user)
    {
        $user = new Yii::$app->controller->module->userModelClass;

        $ignored_users = [];
        foreach (IgnoreListEntry::find()->select('user_id')->where(['blocks_user_id' => $for_user])->asArray()->all() as $ignore)
            $ignored_users[] = $ignore['user_id'];

        $allowed_contacts = [];
        foreach (AllowedContacts::find()->select('is_allowed_to_write')->where(['user_id' => $for_user])->all() as $allowed_user)
            $allowed_contacts[] = $allowed_user->is_allowed_to_write;

        $users = $user::find();
        $users->where(['!=', 'id', Yii::$app->user->id]);
        $users->andWhere(['not in', 'id', $ignored_users]);

        if ($allowed_contacts)
            $users->andWhere(['id' => $allowed_contacts]);

        $users = $users->all();

        if (is_callable(Yii::$app->getModule('message')->recipientsFilterCallback))
            $users = call_user_func(Yii::$app->getModule('message')->recipientsFilterCallback, $users);

        return $users;
    }

    /**
     * Get all Users that have ever written a message to the given user
     * @param $user_id the user to check for
     * @return array the users that have written him
     */
    public static function userFilter($user_id)
    {
        return ArrayHelper::map(
            Message::find()
                ->where(['to' => $user_id])
                ->select('from')
                ->groupBy('from')
                ->all(), 'from', 'sender.username');
    }

    /**
     * @param $user_id
     * @return array|null|Message|ActiveRecord
     */
    public static function getSignature($user_id)
    {
        return Message::find()->where([
            'from' => $user_id,
            'status' => Message::STATUS_SIGNATURE,
        ])->one();
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title', 'message', 'context'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['to'], IgnoreListValidator::class],
            [['to'], 'exist',
                'targetClass' => Yii::$app->getModule('message')->userModelClass,
                'targetAttribute' => 'id',
                'message' => Yii::t('app', 'Recipient has not been found'),
            ],
            [['to'], 'required', 'when' => function ($model) {
                return $model->status != Message::STATUS_SIGNATURE && $model->status != Message::STATUS_DRAFT;
            }],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::class,
                'attributes' => [ActiveRecord::EVENT_BEFORE_INSERT => 'hash'],
                'value' => md5(uniqid(rand(), true)),
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * Send E-Mail to recipients if configured.
     * @param $insert
     * @param $changedAttributes
     * @return mixed
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && isset($this->recipient->email)) {
            $mailMessages = Yii::$app->getModule('message')->mailMessages;

            if ($mailMessages === true
                || (is_callable($mailMessages) && call_user_func($mailMessages, $this->recipient))) {
                $this->sendEmailToRecipient();
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * The new message should be send to the recipient via e-mail once.
     * By default, Yii::$app->mailer is used to do so.
     * If you want do enqueue the mail in an queue like yii2-queue or nterms/yii2-mailqueue you
     * can configure this in the module configuration.
     * You can configure your application specific mail views using themeMap.
     *
     * @see https://github.com/yiisoft/yii2-queue
     * @see https://github.com/nterms/yii2-mailqueue
     * @see http://www.yiiframework.com/doc-2.0/yii-base-theme.html
     */
    public function sendEmailToRecipient()
    {
        $mailer = Yii::$app->{Yii::$app->getModule('message')->mailer};

        $this->trigger(Message::EVENT_BEFORE_MAIL);

        if (!file_exists($mailer->viewPath)) {
            $mailer->viewPath = '@vendor/thyseus/yii2-message/mail/';
        }

        $mailing = $mailer->compose(['html' => 'message', 'text' => 'text/message'], [
            'model' => $this,
            'content' => $this->message
        ])
            ->setTo($this->recipient->email)
            ->setFrom(Yii::$app->params['adminEmail'])
            ->setSubject($this->title);

        if (is_a($mailer, 'nterms\mailqueue\MailQueue')) {
            $mailing->queue();
        } else if (Yii::$app->getModule('message')->useMailQueue) {
            Yii::$app->queue->push(new EmailJob([
                'mailing' => $mailing,
            ]));
        } else {
            $mailing->send();
        }

        $this->trigger(Message::EVENT_AFTER_MAIL);
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('message', '#'),
            'from' => Yii::t('message', 'from'),
            'to' => Yii::t('message', 'to'),
            'title' => Yii::t('message', 'title'),
            'message' => Yii::t('message', 'message'),
            'created_at' => Yii::t('message', 'created at'),
            'context' => Yii::t('message', 'context'),
        ];
    }

    /** We need to avoid the "Serialization of 'Closure'" is not allowed exception
     * when sending the serialized message object to the queue */
    public function __sleep()
    {
        return [];
    }

    /**
     * Never delete the message physically on the database level. It should always stay in the 'sent' folder of the sender.
     * @return int
     */
    public function delete()
    {
        return $this->updateAttributes(['status' => Message::STATUS_DELETED]);
    }

    public function getRecipientLabel()
    {
        if (!$this->recipient)
            return Yii::t('message', 'Removed user');
        else
            return $this->recipient->username;
    }

    public function getAllowedContacts()
    {
        return $this->hasOne(AllowedContacts::class, ['id' => 'user_id']);
    }

    public function getRecipient()
    {
        return $this->hasOne(Yii::$app->getModule('message')->userModelClass, ['id' => 'to']);
    }

    public function getSender()
    {
        return $this->hasOne(Yii::$app->getModule('message')->userModelClass, ['id' => 'from']);
    }

}
