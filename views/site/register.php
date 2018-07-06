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
use yii\helpers\Url;
use yii\captcha\Captcha;

$this->title = '账号注册';
?>
<div class="col-md-4">
<?php $form = ActiveForm::begin([
    'options' => [
        'class' => 'fh5co-form animate-box',
        'data-animate-effect' => 'fadeInLeft',
        'autocomplete' => 'off'
    ]
]) ?>
<h2><?= $this->title ?></h2>
    <div class="form-group">
        <div class="alert alert-danger error-message" role="alert" style="padding:5px 10px;display: none;margin-bottom: 5px;"></div>
    </div>
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
    <!-- VerifyCode -->
    <?= $form->field($model, 'verifyCode',[
        'enableAjaxValidation' => true,
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
    <!-- Code -->
    <?= $form->field($model, 'code',[
        'enableAjaxValidation' => true,
        'template' => '{input}<span class="input-group-addon" style="padding:0;border:none;"><button class="btn btn-xs btn-primary send-code" type="button">发送验证码</button></span>',
        'options' => ['class' => 'form-group input-group'],
        'inputOptions' => [
            'class' => 'form-control sr-only',
            'placeholder' => '邮箱验证码',
            'autocomplete' => 'off',
            'maxlength' => 6,
            'style' => 'color: #333',
        ]
    ]) ?>
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
$usernameId = Html::getInputId($model, 'username');
$verifyCodeId = Html::getInputId($model, 'verifyCode');
$sendCodeUrl = Url::toRoute(['site/register-code']);
$js = <<<JS
    jQuery("#{$form->id}").on("afterValidate",
        function(messages, errorAttributes){
            var message = '';
            try{
                for(var i in errorAttributes){
                    if(errorAttributes[i][0]){
                        message = errorAttributes[i][0];
                        break;
                    }
                }
                if(message.length > 0){
                    $('.error-message').html(message).show();
                }
            }catch(e){
            }
        }
   );

    $('.send-code').on('click', function(){
        var cardTimer;
        $.ajax({
            type: 'post',
            url: '{$sendCodeUrl}',
            dataType: 'json',
            data: {username: $('#{$usernameId}').val(), verifyCode: $('#{$verifyCodeId}').val()},
            beforeSend: function(){
                $('.send-code').attr('disabled', 'disabled').html('正在发送...');
            },
            success: function(data){
                if(data.code){
                    var i = 60;
                    clearInterval(cardTimer);
                    $('.send-code').attr('disabled', 'disabled');
                    cardTimer = setInterval(function(){
                        $('.send-code').html('重新发送(' + buZe(i) + 's)');
                        i--;
                        if(i==0){
                            clearInterval(cardTimer);
                            $('.send-code').removeAttr('disabled').html('发送验证码');
                        }
                    },1000);
                }else{
                    $('.error-message').html(data.message).show();
                    $('.send-code').removeAttr('disabled').html('发送验证码');
                }
            },
            error: function(){
                $('.error-message').html('网络异常。').show();
                $('.send-code').removeAttr('disabled').html('发送验证码');
            }
        });
    });
    
    function buZe(num){
        if(num>0 && num<=9){
            return '0'+num
        }
        return num;
    }
JS;
$this->registerJs($js);
?>
</div>
