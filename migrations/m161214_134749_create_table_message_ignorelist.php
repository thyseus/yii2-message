<?php

use yii\db\Migration;
use yii\db\Schema;

class m161214_134749_create_table_message_ignorelist extends Migration
{
    public function up()
    {
        $tableOptions = '';

        if (Yii::$app->db->driverName == 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%message_ignorelist}}', [
            'user_id'        => Schema::TYPE_INTEGER,
            'blocks_user_id' => Schema::TYPE_INTEGER,
            'created_at'     => Schema::TYPE_DATETIME . ' NOT NULL',
        ], $tableOptions);

        $this->addPrimaryKey('message_ignorelist-pk', '{{%message_ignorelist}}', ['user_id', 'blocks_user_id']);
    }

    public function down()
    {
        $this->dropTable('{{%message_ignorelist}}');
    }
}
