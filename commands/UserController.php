<?php

declare(strict_types=1);


namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

class UserController extends Controller
{
    /**
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function actionRegister($username, $email, $password, $role = 'user')
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
        $user->role = ($role === 'admin') ? 1 : 0;

        if ($user->save()) {
            echo "Пользователь успешно зарегистрирован.\n";
        } else {
            echo "Ошибка: " . implode(", ", $user->getErrors()) . "\n";
        }
    }
}