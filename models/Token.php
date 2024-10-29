<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

class Token extends ActiveRecord
{
    public static function tableName()
    {
        return 'tokens';
    }

    public function rules()
    {
        return [
            [['user_id', 'token', 'expires_at'], 'required'],
            ['token', 'unique'],
        ];
    }
}