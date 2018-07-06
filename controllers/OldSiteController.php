<?php

namespace app\controllers;

use app\components\helpers\ScHelper;
use app\models\PasswordFind;
use app\models\User;
use Yii;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\models\LoginForm;
use yii\bootstrap\ActiveForm;

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
                        'actions' => ['logout'],
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
            ],
        ];
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
     * 账号注册
     * @return array|string|Response
     */
    public function actionRegister()
    {
        $this->view->title = '账号注册';
        $model = new User();
        $request = Yii::$app->request;
        /*if($request->isAjax && $model->load($request->post())){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }*/

        if ($model->load($request->post()) && $model->validate()) {
            // 发送邮件
            $this->sendRegisterMail($model);
            $mailDomain = 'http://mail.' . explode('@', $model->username)[1];
            $content = <<<EOD
                <p>您的账号<a>{$model->username}</a>已经注册成功啦！请到您的邮箱进行验证激活。</p>
    <p><a href="{$mailDomain}" class="btn btn-info">点击激活</a></p>
EOD;

            return $this->render('active', [
                'content' => $content
            ]);
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * 验证
     * @throws NotFoundHttpException
     */
    public function actionActive()
    {
        $token = Yii::$app->request->get('token');
        $data = ScHelper::decode($token);
        if(!$data || !isset($data['method'])){
            throw  new NotFoundHttpException('非法请求！');
        }
        $method = $data['method'];

        if(!method_exists($this, $method)){
            throw  new NotFoundHttpException('非法请求！');
        }

        $content = $this->$method($data);
        return $this->render('active', [
            'content' => $content
        ]);
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
            // 发送邮件
            $this->sendPasswordFindMail($model);
            $mailDomain = 'http://mail.' . explode('@', $model->username)[1];
            $content = <<<EOD
                <p>您的账号<a>{$model->username}</a>已经申请密码找回啦！请到您的邮箱进行密码找回。</p>
    <p><a href="{$mailDomain}" class="btn btn-info">找回密码</a></p>
EOD;

            return $this->render('active', [
                'content' => $content
            ]);
        }

        return $this->render('password-find', [
            'model' => $model,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $request = Yii::$app->request;
        if(!Yii::$app->user->isGuest){
            return $this->redirect($request->get('redirect_url', DEFAULT_REDIRECT_URL));
        }

        $model = new LoginForm();
        if ($model->load($request->post()) && $model->login()) {
            return $this->redirect($request->get('redirect_url', DEFAULT_REDIRECT_URL));
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(Yii::$app->request->get('redirect_url', ['site/login']));
    }

    /**
     * 验证注册
     * @param $data
     * @return string
     */
    private function register($data)
    {
        $this->view->title = '账号激活';
        $url = Url::toRoute(['site/register']);
        // 激活时间超时
        if($data['time'] < time()){
            $content = <<<EOD
                <p>您的账号<a>{$data['username']}</a>激活时间已经过期。请重新进行账号注册。</p>
                <p><a href="{$url}" class="btn btn-info">账号注册</a></p>
EOD;
        }else{
            /* @var $model User */
            $model = Yii::$app->cache->get(md5($data['username']));
            if($model){
                // 注册成功
                if($model->save(false)){
                    $url = Url::toRoute(['site/login']);
                    $content = <<<EOD
                        <p>您的账号<a>{$data['username']}</a>激活成功。</p>
                        <p><a href="{$url}" class="btn btn-info">账号登录</a></p>
EOD;
                }else{ // 注册失败
                    $content = <<<EOD
                        <p>您的账号<a>{$data['username']}</a>激活失败。请重新进行账号注册。</p>
                        <p><a href="{$url}" class="btn btn-info">账号注册</a></p>
EOD;
                }
                // 删除key
                Yii::$app->cache->delete(md5($data['username']));
            }else{ // 账号对象异常
                $content = <<<EOD
                    <p>您的账号<a>{$data['username']}</a>注册激活链接已失效。请重新进行账号注册。</p>
                    <p><a href="{$url}" class="btn btn-info">账号注册</a></p>
EOD;
            }
        }
        return $content;
    }

    /**
     * 验证密码找回
     * @param $data
     * @return string
     */
    private function passwordFind($data)
    {
        $this->view->title = '密码找回';
        // 激活时间超时
        $url = Url::toRoute(['site/password-find']);
        if($data['time'] < time()){
            $content = <<<EOD
                <p>您的账号<a>{$data['username']}</a>密码找回已经过期。请重新进行密码找回。</p>
                <p><a href="{$url}" class="btn btn-info">密码找回</a></p>
EOD;
        }else{
            /* @var $model User */
            $model = Yii::$app->cache->get(md5($data['username']));
            if($model){
                // 找回成功
                if($model->save(false)){
                    $url = Url::toRoute(['site/login']);
                    $content = <<<EOD
                        <p>您的账号<a>{$data['username']}</a>密码找回成功。</p>
                        <p><a href="{$url}" class="btn btn-info">账号登录</a></p>
EOD;
                }else{ // 找回失败
                    $content = <<<EOD
                        <p>您的账号<a>{$data['username']}</a>密码找回失败。请重新进行密码找回。</p>
                        <p><a href="{$url}" class="btn btn-info">密码找回</a></p>
EOD;
                }
                // 删除key
                Yii::$app->cache->delete(md5($data['username']));
            }else{ // 账号对象异常
                $content = <<<EOD
                    <p>您的账号<a>{$data['username']}</a>密码找回链接已失效。请重新进行密码找回。</p>
                    <p><a href="{$url}" class="btn btn-info">密码找回</a></p>
EOD;
            }
        }
        return $content;
    }

    /**
     * 发送注册邮件
     * @param $model User
     */
    private function sendRegisterMail($model)
    {
        $token = ScHelper::encode(['username' => $model->username, 'time' => time() + 1800, 'method' => 'register']);
        Yii::$app->cache->set(md5($model->username), $model, 1800);
        $content = '恭喜您已注册账号成功！点击' . Html::a('激活', Url::toRoute(['site/active', 'token' => $token], true)) . '。(有效时间为30分钟)';
        $this->sendMail($model->username, '账号激活', $content);

    }

    /**
     * 发送密码找回邮件
     * @param $model User
     */
    private function sendPasswordFindMail($model)
    {
        $token = ScHelper::encode(['username' => $model->username, 'time' => time() + 1800, 'method' => 'passwordFind']);
        Yii::$app->cache->set(md5($model->username), $model, 1800);
        $content = '恭喜您已成功成功申请密码找回！点击' . Html::a('找回密码', Url::toRoute(['site/active', 'token' => $token], true)) . '修改密码。(有效时间为30分钟)';
        $this->sendMail($model->username, '找回密码', $content);

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
        $mail = Yii::$app->mailer->compose('site/mail',[
            'html' => 'contact-html',
            'content' => $content
        ]);
        // 接收方邮箱
        $mail->setTo($email);
        // 邮件标题
        $mail->setSubject($title);
        // 发送图片
        //$mail->attach('图片可访问地址');
        // 发送附件
        //$mail->attachContent('Attachment content', ['fileName' => 'attach.txt', 'contentType' => 'text/plain']);
        // html文本内容
        //$mail->setHtmlBody($email->content);
        // 纯文本内容
        //$mail->setTextBody($email->content);
        // 邮件标题
        // $mail->setSubject('找回密码');
        // 发送图片
        //$mail->attach(Yii::getAlias('@static') . '/image/3dfea9737dd042f38959135624efb0d0_th.jpeg');
        // 发送附件
        //$mail->attach(Yii::getAlias('@static') . '/file/20170424-20170430订单流水明细.xlsx');

        return $mail->send();
    }
}
