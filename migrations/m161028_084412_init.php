<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * @author Herbert Maschke <thyseus@gmail.com
 */
class m161028_084412_init extends Migration
{
    public function up()
    {
        $tableOptions = '';

        if (Yii::$app->db->driverName == 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%message}}', [
            'id'                   => Schema::TYPE_PK,
            'hash'                 => Schema::TYPE_STRING . '(32) NOT NULL',
            'from'                 => Schema::TYPE_INTEGER,
            'to'                   => Schema::TYPE_INTEGER,
            'status'               => Schema::TYPE_INTEGER,
            'title'                => Schema::TYPE_STRING . '(255) NOT NULL',
            'message'              => Schema::TYPE_TEXT,
            'created_at'           => Schema::TYPE_DATETIME . ' NOT NULL',
        ], $tableOptions);

    }

    public function down()
    {
        $this->dropTable('{{%message}}');
    }
}
