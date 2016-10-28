<?php

/**
 * This is the model class for yii2-message.
 *
 */

namespace thyseus\message\models;

use yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;


class Message extends ActiveRecord
{
    const STATUS_UNREAD = 0;
    const STATUS_READ = 1;
    const STATUS_ANSWERED = 2;

    public static function tableName()
    {
        return '{{%message}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function beforeValidate()
    {
        $to_user = new Yii::$app->controller->module->userModelClass;

        if (!$to_user->findOne($this->to))
            $this->addError('to', Yii::t('message', 'Recipient has not been found'));

        if (!$this->from)
            $this->from = Yii::$app->user->id;

        if (!$this->hash)
            $this->hash = md5(uniqid(rand(), true));

        return parent::beforeValidate();
    }

    public function rules()
    {
        return [
            [['from', 'to', 'title'], 'required'],
            [['from', 'to'], 'integer'],
            [['title', 'message'], 'string'],
            [['title'], 'string', 'max' => 255]
        ];
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

    public function getRecipient()
    {
        return $this->hasOne(Yii::$app->controller->module->userModelClass, ['id' => 'to']);
    }

    public function getSender()
    {
        return $this->hasOne(Yii::$app->controller->module->userModelClass, ['id' => 'from']);
    }

}
