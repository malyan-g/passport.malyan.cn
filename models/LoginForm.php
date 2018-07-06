<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $verifyCode;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password', 'verifyCode'], 'required'],
            [['username'], 'string', 'max' => 20],
            [['username'], 'email'],
            [['password'], 'string', 'min' => 8, 'max' => 20],
            [['password'], 'match', 'pattern' =>'/^[a-zA-Z0-9]+$/u', 'message' => '{attribute}只能为字母和数字。'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['verifyCode', 'validateVerifyCode'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'verifyCode' => '验证码',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '用户名或密码错误。');
            }else{
                if($user->status !== User::STATUS_ACTIVE){
                    $this->addError($attribute, '此用户已被禁用。');
                }
            }
        }
    }

    /**
     * 验证码
     * @param $attribute
     * @param $params
     */
    public function validateVerifyCode($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $captchaValidate  = new \yii\captcha\CaptchaAction('loginCaptcha', Yii::$app->controller);
            $verifyCode = $captchaValidate->getVerifyCode();
            if(strcasecmp($this->$attribute, $verifyCode) !== 0){
                $this->addError($attribute, '验证码错误。');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
