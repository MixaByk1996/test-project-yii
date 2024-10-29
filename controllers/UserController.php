<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Token;
use app\models\User;
use Yii;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class UserController extends Controller
{
    public function actionLogin()
    {
        $body = Yii::$app->request->post();
        $user = User::findOne(['email' => $body['email']]);

        if (!$user || !Yii::$app->security->validatePassword($body['password'], $user->password_hash)) {
            throw new UnauthorizedHttpException('Invalid credentials');
        }

        $token = new Token();
        $token->user_id = $user->id;
        $token->token = Yii::$app->security->generateRandomString();
        $expiry = new \DateTime();
        $expiry->modify('+1 hour');
        $token->expires_at = $expiry->format('Y-m-d H:i:s');
        $token->save();

        return ['token' => $token->token];
    }

    /**
     * @throws UnauthorizedHttpException
     */
    public function actionMe(): ?User
    {
        $request = Yii::$app->request;
        $headers = $request->headers;

        $tokenValue = $headers->get('Authorization');
        if (!$tokenValue) {
            throw new UnauthorizedHttpException('Не авторизирован');
        }

        $tokenValue = str_replace('Bearer ', '', $tokenValue);
        $token = Token::findOne(['token' => $tokenValue]);

        if ($token && $token->expires_at > date('Y-m-d H:i:s')) {
            $user = User::findOne($token->user_id);
            return $user;
        }

        throw new UnauthorizedHttpException('Истек срок действия токена');
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws UnauthorizedHttpException
     */
    public function actionIndex(): array
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new UnauthorizedHttpException('У вас нету прав!');
        }

        return User::find()->all();
    }

    /**
     * @return User
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     * @throws UnauthorizedHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreate(): User
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new UnauthorizedHttpException('У вас нету прав!');
        }

        $user = new User();
        $user->load(Yii::$app->request->post(), '');
        $user->password_hash = Yii::$app->security->generatePasswordHash($user->password_hash);
        $user->created_at = date('Y-m-d H:i:s');
        if (!$user->save()) {
            throw new BadRequestHttpException(implode(", ", $user->getErrorSummary(true)));
        }

        return $user;
    }

    /**
     * @param $id
     * @return User
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id): User
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new UnauthorizedHttpException('У вас нету прав!');
        }

        $user = User::findOne($id);
        if (!$user) {
            throw new BadRequestHttpException('Пользователь не найден');
        }

        $user->load(Yii::$app->request->post(), '');
        if (!$user->save()) {
            throw new BadRequestHttpException(implode(", ", $user->getErrorSummary(true)));
        }

        return $user;
    }

    public function actionDelete($id)
    {
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new UnauthorizedHttpException('У вас нету прав!');
        }

        $user = User::findOne($id);
        if ($user) {
            $user->delete();
            return ['status' => 'success', 'message' => 'Данные пользователя удалены!'];
        }

        throw new BadRequestHttpException('Пользователь не найден!');
    }
}