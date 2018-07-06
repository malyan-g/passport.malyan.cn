<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/3/29
 * Time: 下午9:57
 */

namespace app\models;

use Yii;

class PasswordFind extends User
{
    public static $codeNamePrefix = 'FIND.PASSWORD.';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            ['username', 'email'],
            ['username', 'validateUsername'],
            ['verifyCode', 'required'],
            ['verifyCode', 'validateVerifyCode'],
            ['code', 'required'],
            ['code', 'validateCode'],
            ['password', 'required'],
            [['password'], 'string', 'min' => 8, 'max' => 20],
            [['password'], 'match', 'pattern' => '/^[a-zA-Z0-9]+$/u', 'message' => '{attribute}只能为字母和数字。'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '邮箱',
            'password' => '新密码',
            'verifyCode' => '验证码',
            'code' => '邮箱验证码',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUsername($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = self::findByUsername($this->username);

            if (!$user) {
                $this->addError($attribute, '邮箱不存在。');
            }
        }
    }
}
