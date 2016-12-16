<?php

/**
 * This is the model class for yii2-message.
 *
 */

namespace thyseus\message\models;

use thyseus\message\validators\IgnoreListValidator;
use yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;


class Message extends ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_UNREAD = 0;
    const STATUS_READ = 1;
    const STATUS_ANSWERED = 2;

    const EVENT_BEFORE_MAIL = 'before_mail';
    const EVENT_AFTER_MAIL = 'after_mail';

    public static function tableName()
    {
        return '{{%message}}';
    }

    public function rules()
    {
        return [
            [['to', 'title'], 'required'],
            [['to'], 'integer'],
            [['title', 'message'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['to'], IgnoreListValidator::className()],
            [['to'], 'exist',
                'targetClass' => Yii::$app->getModule('message')->userModelClass,
                'targetAttribute' => 'id',
                'message' => Yii::t('message', 'Recipient has not been found'),
            ]
        ];
    }

    public static function compose($from, $to, $title, $message = '')
    {
        $model = new Message;
        $model->from = $from;
        $model->to = $to;
        $model->title = $title;
        $model->message = $message;
        return $model->save();
    }

    public static function getPossibleRecipients($for_user)
    {
        $user = new Yii::$app->controller->module->userModelClass;

        $ignored_users = [];

        foreach(IgnoreListEntry::find()->select('user_id')->where(['blocks_user_id' => $for_user])->asArray()->all() as $ignore)
            $ignored_users[] = $ignore['user_id'];

        $users = $user::find()->where(['!=', 'id', Yii::$app->user->id])->andWhere(['not in', 'id', $ignored_users])->all();

        if(is_callable(Yii::$app->getModule('message')->recipientsFilterCallback))
            $users = call_user_func(Yii::$app->getModule('message')->recipientsFilterCallback, $users);

        return $users;
    }

    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [ActiveRecord::EVENT_BEFORE_INSERT => 'hash'],
                'value' => md5(uniqid(rand(), true)),
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    // BlameableBehavior can not be used because the ignoreListValidator needs to have 'from' filled at validation time.
    public function beforeValidate()
    {
        if (!$this->from)
            $this->from = Yii::$app->user->id;

        return parent::beforeValidate();
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && isset($this->recipient->email)) {
            $mailMessages = Yii::$app->getModule('message')->mailMessages;
            if ($mailMessages === true || (is_callable($mailMessages) && $mailMessages($this->recipient)))
                $this->sendEmailToRecipient();
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    // returns an array of possible recipients for the given user. Applies the ignorelist and applies possible custom
    // logic.

    public function sendEmailToRecipient()
    {
        $this->trigger(Message::EVENT_BEFORE_MAIL);

        Yii::$app->mailer->compose()
            ->setTo($this->recipient->email)
            ->setFrom(Yii::$app->params['adminEmail'])
            ->setSubject($this->title)
            ->setHtmlBody($this->message)
            ->send();

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
            'created_at' => Yii::t('message', 'sent at'),
        ];
    }

    public function delete()
    {
        return $this->updateAttributes(['status' => Message::STATUS_DELETED]);
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
