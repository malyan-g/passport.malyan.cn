<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use app\models\LoginForm;
use yii\bootstrap\ActiveForm;
use yii\base\DynamicModel;
use yii\captcha\CaptchaAction;
use app\models\User;
use app\models\PasswordFind;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout', 'user-info'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'loginCaptcha' => [
                'class' => 'app\components\captcha\CaptchaAction'
            ],
            'registerCaptcha' => [
                'class' => 'app\components\captcha\CaptchaAction'
            ],
            'passwordCaptcha' => [
                'class' => 'app\components\captcha\CaptchaAction'
            ]
        ];
    }

    public function actionIdentity()
    {

        $callback = Yii::$app->request->get('callback', '');
        // callback认证
        if($this->authCallback($callback) && $this->authDomain()){
            Yii::$app->response->format = Response::FORMAT_JSONP;
            $data = Yii::$app->user->isGuest ? '' : Yii::$app->user->identity;
            return [
                'callback' => Yii::$app->request->get('callback'),
                'data' => $data
            ];
        }

        return $this->redirect(DEFAULT_REDIRECT_URL);
    }

    public function actionUser()
    {
        // callback认证
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->user->isGuest ? '' : Yii::$app->user->identity;
        return $data;
    }

    private function authCallback($callback)
    {
        $callbackMap = ['login', 'identity'];
        return in_array($callback, $callbackMap);
    }

    private function authDomain()
    {
        $domainMap = [
            'http://localhost/',
            'http://www.malyan.cn/',
            'http://blog.malyan.cn/'
        ];
        $domain = Yii::$app->request->referrer;
        return in_array($domain, $domainMap);
    }

    /**
     * 跳转到登录页
     * @return Response
     */
    public function actionIndex()
    {
        return $this->redirect(['site/login']);
    }

    /**
     * 账号登录
     * @return string|Response
     */
    public function actionLogin()
    {
        $request = Yii::$app->request;
        if(!Yii::$app->user->isGuest){
            return $this->redirect($request->get('redirect_url', DEFAULT_REDIRECT_URL));
        }

        $model = new LoginForm();
        if($request->isAjax && $model->load($request->post())){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($request->post()) && $model->login()) {
            return $this->redirect($request->get('redirect_url', DEFAULT_REDIRECT_URL));
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * 账号退出
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(Yii::$app->request->get('redirect_url', ['site/login']));
    }

    /**
     * 账号注册
     * @return array|string|Response
     */
    public function actionRegister()
    {
        $model = new User();
        $request = Yii::$app->request;
        if($request->isAjax && $model->load($request->post())){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($request->post()) && $model->validate()) {
            Yii::$app->cache->delete(md5(User::$codeNamePrefix . $model->username));
            return $this->save($model, '账号注册');
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * 账号注册验证码
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRegisterCode()
    {
        $this->enableCsrfValidation = false;
        $data = ['code' => 1, 'message' => '发送成功'];
        Yii::$app->response->format = Response::FORMAT_JSON;
        $username = Yii::$app->request->post('username');
        $verifyCode = Yii::$app->request->post('verifyCode');
        $model = DynamicModel::validateData([
            'username' => $username,
            'verifyCode' => $verifyCode
        ], [
            ['username', 'required', 'message' => '邮箱不能为空。'],
            ['username', 'string', 'max' => 20, 'message' => '邮箱只能包含至多20个字符。'],
            ['username', 'email', 'message' => '邮箱不是有效的邮箱地址。'],
            ['verifyCode', 'required', 'message' => '验证码不能为空。'],
        ]);
        if($model->errors){
            $data['code'] = 0;
            $data['message'] =current($model->firstErrors);
        }else{
            $captchaValidate  = new CaptchaAction('registerCaptcha', Yii::$app->controller);
            $code = $captchaValidate->getVerifyCode();
            if(strcasecmp($verifyCode, $code) === 0){
                if(User::findByUsername($username)){
                    $data['code'] = 0;
                    $data['message'] = '此邮箱已经注册。';
                }else{
                    $this->sendCode($username,'账号注册', User::$codeNamePrefix);
                }
            }else{
                $data['code'] = 0;
                $data['message'] = '验证码错误。';
            }
        }
        return $data;
    }

    /**
     * 找回密码
     * @return array|string|Response
     */
    public function actionPasswordFind()
    {
        $this->view->title = '找回密码';
        $model = new PasswordFind();
        $request = Yii::$app->request;
        if($request->isAjax && $model->load($request->post())){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($request->post()) && $model->validate()) {
            $model = PasswordFind::findByUsername($model->username);
            $model->load($request->post());
            Yii::$app->cache->delete(md5(PasswordFind::$codeNamePrefix . $model->username));
            return $this->save($model, '找回密码');
        }

        return $this->render('password-find', [
            'model' => $model,
        ]);
    }

    /**
     * 找回密码验证码
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPasswordCode()
    {
        $data = ['code' => 1, 'message' => '发送成功'];
        Yii::$app->response->format = Response::FORMAT_JSON;
        $username = Yii::$app->request->post('username');
        $verifyCode = Yii::$app->request->post('verifyCode');
        $model = DynamicModel::validateData([
            'username' => $username,
            'verifyCode' => $verifyCode
        ], [
            ['username', 'required', 'message' => '邮箱不能为空。'],
            ['username', 'string', 'max' => 20, 'message' => '邮箱只能包含至多20个字符。'],
            ['username', 'email', 'message' => '邮箱不是有效的邮箱地址。'],
            ['verifyCode', 'required', 'message' => '验证码不能为空。'],
        ]);
        if($model->errors){
            $data['code'] = 0;
            $data['message'] =current($model->firstErrors);
        }else{
            $captchaValidate  = new CaptchaAction('passwordCaptcha', Yii::$app->controller);
            $code = $captchaValidate->getVerifyCode();
            if(strcasecmp($verifyCode, $code) === 0){
                if(!User::findByUsername($username)){
                    $data['code'] = 0;
                    $data['message'] = '此邮箱不存在。';
                }else{
                    $this->sendCode($username,'找回密码', PasswordFind::$codeNamePrefix);
                }
            }else{
                $data['code'] = 0;
                $data['message'] = '验证码错误。';
            }
        }
        return $data;
    }

    /**
     * 保存
     * @param User $model
     * @return string
     */
    private function save($model, $message)
    {
        if($model->save(false)){
            $mailDomain = Url::toRoute(['site/login'], true);
            $content = <<<EOD
                <p>您的账号<a>{$model->username}</a>{$message}成功啦！</p>
    <p><a href="{$mailDomain}" class="btn btn-info">账号登录</a></p>
EOD;
        }else{
            $mailDomain = 'Mailto:master@malyan.cn';
            $content = <<<EOD
                <p>您的账号<a>{$model->username}</a>{$message}失败！请您联系管理人员。</p>
    <p><a href="{$mailDomain}" class="btn btn-info">联系管理人员</a></p>
EOD;
        }

        return $this->render('active', [
            'content' => $content
        ]);
    }

    /**
     * 发送验证码
     * @param $username
     * @param $title
     * @param $name
     */
    private function sendCode($username, $title, $name)
    {
        // 发送验证码
        $verifyCode = rand(100000, 999999);
        Yii::$app->cache->set(md5($name . $username), $verifyCode, 600);
        $content = '【Malyan】您的' . $title . '验证码为：' .  $verifyCode . '；验证码10分钟内有效，请您尽快完成验证。(如非本人操作请忽略)';
        $this->sendMail($username, $title, $content);
    }

    /**
     * 发送邮件
     * @param $email
     * @param $title
     * @param $content
     * @return string
     */
    private function sendMail($email, $title, $content)
    {
        return Yii::$app->mailer->compose('site/mail',
            [
                'html' => 'contact-html',
                'content' => $content
            ]
        )->setTo($email)->setSubject($title)->send();
    }
}
