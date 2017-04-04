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
        return 'message_allowed_contacts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'is_allowed_to_write'], 'unique'],
            [['user_id', 'is_allowed_to_write'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
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
