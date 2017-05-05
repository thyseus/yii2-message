<?php

namespace thyseus\message\models;

use Yii;

/**
 * This is the model class for table "message_ignorelist".
 *
 * @property integer $user_id
 * @property integer $blocks_user_id
 * @property string $created_at
 */
class IgnoreListEntry extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message_ignorelist}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'blocks_user_id', 'created_at'], 'required'],
            [['user_id', 'blocks_user_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('message', 'User ID'),
            'blocks_user_id' => Yii::t('message', 'Blocks User ID'),
            'created_at' => Yii::t('message', 'Created At'),
        ];
    }
}
