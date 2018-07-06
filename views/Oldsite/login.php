<?php

/* @var $model \app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;


$this->title = '账号登录';
?>
<div class="col-md-4">
<?php $form = ActiveForm::begin([
    'errorCssClass' => false,
    'successCssClass' => false,
    'enableClientValidation' => false,
    'options' => [
        'class' => 'fh5co-form animate-box',
        'data-animate-effect' => 'fadeInLeft'
    ]
]) ?>
    <h2>账号登录</h2>
    <?php if($model->firstErrors): ?>
        <div class="form-group">
            <div class="alert alert-danger error-message" role="alert" style="padding:5px 10px;"><?= current($model->firstErrors) ?></div>
        </div>
    <?php endif ?>
    <!-- Username -->
    <?= $form->field($model, 'username',[
        'template' => '{label}{input}',
        'labelOptions' => [
            'class' => 'sr-only',
        ],
        'inputOptions' => [
            'class' => 'form-control',
            'placeholder' => '请输入邮箱',
            'autocomplete' => 'off'
        ]
    ]) ?>
    <!-- Password -->
    <?= $form->field($model, 'password',[
        'template' => '{label}{input}',
        'labelOptions' => [
            'class' => 'sr-only',
        ],
        'inputOptions' => [
            'class' => 'form-control',
            'placeholder' => '请输入密码',
            'autocomplete' => 'off'
        ]
    ])->passwordInput() ?>
    <!-- VerifyCode -->
    <?= $form->field($model, 'verifyCode',[
        'template' => '{input}',
        'options' => ['class' => 'form-group input-group'],
    ])->widget(Captcha::className(), [
        'captchaAction' => 'site/loginCaptcha',
        'template' => '{input}<span class="input-group-addon" style="padding:0;border:none;">{image}</span>',
        'options' => [
            'class' => 'form-control sr-only',
            'placeholder' => '请输入验证码',
            'autocomplete' => 'off',
            'maxlength' => 4,
            'style' => 'color: #333',
        ],
        'imageOptions' => [
            'id' => 'login-captcha',
            'style' => 'cursor: pointer;border:none',
            'title' => '看不清？点击图片更换'
        ],
    ]) ?>
    <!-- rememberMe -->
    <?= $form->field($model, 'rememberMe')->checkbox([
        'template' => '{input}{label}',
    ])->label(' 记住密码') ?>
    <div class="form-group">
        <p>
            没有帐号?
            <?= Html::a('注册', ['site/register']) ?>
             |
            <?= Html::a('忘记密码?', ['site/password-find']) ?>
        </p>
    </div>
    <div class="form-group">
        <?= Html::submitInput('登录', ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end() ?>
</div>
