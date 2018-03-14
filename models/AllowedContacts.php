<?php

namespace thyseus\message\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "message_allowed_contacts".
 *
 * @property int $user_id
 * @property int $is_allowed_to_write
 * @property string $created_at
 * @property string $updated_at
 */
class AllowedContacts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message_allowed_contacts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'is_allowed_to_write'], 'integer'],
        ];
    }

    public function beforeValidate()
    {
        if (self::findOne(['user_id' => $this->user_id, 'is_allowed_to_write' => $this->is_allowed_to_write])) {
            $this->addError('user_id', 'User ' . $this->user_id . ' is already allowed to write to user ' . $this->is_allowed_to_write);
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d G:i:s'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'is_allowed_to_write' => 'Is Allowed To Write',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
