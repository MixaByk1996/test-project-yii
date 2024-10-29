<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tokens}}`.
 */
class m241028_194022_create_tokens_table extends Migration
{
    public function up()
    {
        $this->createTable('tokens', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string()->unique()->notNull(),
            'expires_at' => $this->timestamp()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),

            // Foreign key
            'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE',
        ]);
    }

    public function down()
    {
        $this->dropTable('tokens');
    }
}
