<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/3/29
 * Time: 下午3:40
 */

/* @var $model \app\models\User */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
?>
<div class="col-md-4">
<?php $form = ActiveForm::begin([
    'options' => [
        'class' => 'fh5co-form animate-box',
        'data-animate-effect' => 'fadeInLeft'
    ]
]) ?>
<h2>账号注册</h2>
<!-- username -->
<?= $form->field($model, 'username',[
    'enableAjaxValidation' => true,
    'template' => '{label}{input}',
    'labelOptions' => [
        'class' => 'sr-only',
    ],
    'inputOptions' => [
        'class' => 'form-control',
        'placeholder' => '邮箱',
        'autocomplete' => 'off'
    ]
]) ?>
<!-- password -->
<?= $form->field($model, 'password',[
    'template' => '{label}{input}',
    'labelOptions' => [
        'class' => 'sr-only',
    ],
    'inputOptions' => [
        'class' => 'form-control',
        'placeholder' => '密码',
        'autocomplete' => 'off'
    ]
])->passwordInput() ?>
    <!-- password_compare -->
<?= $form->field($model, 'password_compare',[
    'template' => '{label}{input}',
    'labelOptions' => [
        'class' => 'sr-only',
    ],
    'inputOptions' => [
        'class' => 'form-control',
        'placeholder' => '确认密码',
        'autocomplete' => 'off'
    ]
])->passwordInput() ?>
<!-- Nickname -->
<?= $form->field($model, 'nickname',[
    'template' => '{label}{input}',
    'labelOptions' => [
        'class' => 'sr-only',
    ],
    'inputOptions' => [
        'class' => 'form-control',
        'placeholder' => '昵称',
        'autocomplete' => 'off'
    ]
]) ?>
<!-- VerifyCode -->
<?= $form->field($model, 'verifyCode',[
    'template' => '{input}',
    'options' => ['class' => 'form-group input-group'],
])->widget(Captcha::className(), [
    'captchaAction' => 'site/registerCaptcha',
    'template' => '{input}<span class="input-group-addon" style="padding:0;border:none;">{image}</span>',
    'options' => [
        'class' => 'form-control sr-only',
        'placeholder' => '验证码',
        'autocomplete' => 'off',
        'maxlength' => 4,
        'style' => 'color: #333',
    ],
    'imageOptions' => [
        'id' => 'register-captcha',
        'style' => 'cursor: pointer;border:none',
        'title' => '看不清？点击图片更换'
    ],
]) ?>
<div class="form-group">
    <p>
        已有帐号?
        <?= Html::a('登录', ['site/login']) ?>
    </p>
</div>
<div class="form-group">
    <?= Html::submitInput('注册', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end() ?>
<?php
$verifyCodeId = Html::getInputId($model, 'verifyCode');
$js = <<<JS
    jQuery("#{$form->id}").on("afterValidate",
        function(messages, errorAttributes){
            var message = [];
            var field = '';
            try{
                for(var i in errorAttributes){
                    if(errorAttributes[i][0]){
                        field = i;
                        message.push(errorAttributes[i][0]);
                        break;
                    }
                }
                if(message.length > 0){
                    layer.msg(message[0], {time: 1500, shade: 0.1}, function(){
                        if(field == 'user-verifycode' && $('#$verifyCodeId').val()){
                            $('#register-captcha').click();
                        }
                    });
                }
            }catch(e){
            }
        }
   );
JS;
$this->registerJs($js);
?>
</div>
