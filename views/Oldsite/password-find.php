<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 18/3/29
 * Time: 下午9:50
 */
/* @var $model \app\models\LoginForm */

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
    <h2>找回密码</h2>
    <!-- Username -->
    <?= $form->field($model, 'username',[
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
            'placeholder' => '新密码',
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
    <!-- VerifyCode -->
    <?= $form->field($model, 'verifyCode',[
        'template' => '{input}',
        'options' => ['class' => 'form-group input-group'],
    ])->widget(Captcha::className(), [
        'captchaAction' => 'site/passwordCaptcha',
        'template' => '{input}<span class="input-group-addon" style="padding:0;border:none;">{image}</span>',
        'options' => [
            'class' => 'form-control sr-only',
            'placeholder' => '验证码',
            'autocomplete' => 'off',
            'maxlength' => 4,
            'style' => 'color: #333',
        ],
        'imageOptions' => [
            'style' => 'cursor: pointer;border:none',
            'title' => '看不清？点击图片更换'
        ],
    ]) ?>
    <div class="form-group">
        <p>
            <?= Html::a('登录', ['site/login']) ?>
            or
            <?= Html::a('注册?', ['site/register']) ?>
    </div>
    <div class="form-group">
        <?= Html::submitInput('确定', ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end() ?>
<?php
$js = <<<JS
    jQuery("#{$form->id}").on("afterValidate",
        function(messages, errorAttributes){
            var message = [];
            try{
                for(var i in errorAttributes){
                    if(errorAttributes[i][0]){
                        message.push(errorAttributes[i][0]);
                    }
                }
                if(message.length > 0){
                    layer.msg(message[0], {time: 1500, shade: 0.1});
                }
            }catch(e){
            }
        }
   );
JS;
$this->registerJs($js);
?>
</div>
