<?php

use yii\db\Migration;
use yii\db\Schema;

class m170203_090001_create_table_message_allowed_contacts extends Migration
{

    public function up()
    {
        $tableOptions = '';

        if (Yii::$app->db->driverName == 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%message_allowed_contacts}}', [
            'user_id'             => Schema::TYPE_INTEGER,
            'is_allowed_to_write' => Schema::TYPE_INTEGER,
            'created_at'          => Schema::TYPE_DATETIME . ' NOT NULL',
            'updated_at'          => Schema::TYPE_DATETIME . ' NOT NULL',
        ], $tableOptions);

        $this->addPrimaryKey('message_allowed_contacts-pk', '{{%message_allowed_contacts}}', ['user_id', 'is_allowed_to_write']);
    }

    public function down()
    {
        $this->dropTable('{{%message_allowed_contacts}}');
    }
}
