<?php
namespace marionnewlevant\snitch\migrations;

use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        // create our table
        $this->createTable('{{%snitch_collisions}}', [
            'id' => $this->primaryKey(),
            'snitchId' => $this->integer()->notNull(),
            'snitchType' => $this->string(),
            'userId' => $this->integer()->notNull(),
            'whenEntered' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // foreign keys: our userId must be a user id
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%snitch_collisions}}', 'userId'),
            '{{%snitch_collisions}}', 'userId', '{{%users}}', 'id', 'CASCADE', null);
    }

    public function safeDown()
    {
        // remove the table on uninstall
        $this->dropTableIfExists('{{%snitch_collisions}}');
    }
}