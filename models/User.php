<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const ROLE_ADMIN = 1;
    const ROLE_USER = 0;

    public static function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return [
            [['username', 'email', 'password_hash', 'role'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            ['role', 'in', 'range' => [self::ROLE_ADMIN, self::ROLE_USER]],
        ];
    }
}