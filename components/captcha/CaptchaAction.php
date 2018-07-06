<?php
/**
 * Created by PhpStorm.
 * User: M
 * Date: 17/6/19
 * Time: 下午3:37
 */

namespace app\components\captcha;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\base\InvalidConfigException;

class CaptchaAction extends \yii\captcha\CaptchaAction
{
    /**
     * @var int the width of the generated CAPTCHA image. Defaults to 120.
     */
    public $width = 96;
    /**
     * @var int the height of the generated CAPTCHA image. Defaults to 50.
     */
    public $height = 48;
    /**
     * @var int padding around the text. Defaults to 2.
     */
    public $backColor = 0XFFFFFF;
    /**
     * @var int the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
     */
    public $foreColor = 0x33CCCC;
    /**
     * @var int the minimum length for randomly generated word. Defaults to 6.
     */
    public $minLength = 4;
    /**
     * @var int the maximum length for randomly generated word. Defaults to 7.
     */
    public $maxLength = 4;
    /**
     * @var int the offset between characters. Defaults to -2. You can adjust this property
     * in order to decrease or increase the readability of the captcha.
     */
    public $offset = 3;

    /**
     * Runs the action.
     */
    /*public function run()
    {
        if (Yii::$app->request->getQueryParam(self::REFRESH_GET_VAR) !== null) {
            // AJAX request for regenerating code
            $code = $this->getVerifyCode(true);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'hash1' => $this->generateValidationHash($code),
                'hash2' => $this->generateValidationHash(strtolower($code)),
                // we add a random 'v' parameter so that FireFox can refresh the image
                // when src attribute of image tag is changed
                'url' => Url::to([$this->id, 'v' => uniqid()]),
            ];
        }else{
            $this->setHttpHeaders();
            Yii::$app->response->format = Response::FORMAT_RAW;

            return $this->renderImage($this->getVerifyCode(true));
        }
    }*/

    public $autoRegenerate = true;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        $array = ['@webroot/fonts/SpicyRice.ttf', '@webroot/fonts/siemens.ttf', '@webroot/fonts/Blenda.otf'];
        $this->fontFile = $array[array_rand($array)];
        $this->fontFile = Yii::getAlias($this->fontFile);
        if (!is_file($this->fontFile)) {
            throw new InvalidConfigException("The font file does not exist: {$this->fontFile}");
        }
    }

    public function run()
    {
        if ($this->autoRegenerate && Yii::$app->request->getQueryParam(self::REFRESH_GET_VAR) === null) {
            $this->setHttpHeaders();
            Yii::$app->response->format = Response::FORMAT_RAW;
            return $this->renderImage($this->getVerifyCode(true));
        }
        return parent::run();
    }
    /**
     * Renders the CAPTCHA image based on the code using GD library.
     * @param string $code the verification code
     * @return string image contents in PNG format.
     */
    protected function renderImageByGD($code)
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $backColor = imagecolorallocate(
            $image,
            (int) ($this->backColor % 0x1000000 / 0x10000),
            (int) ($this->backColor % 0x10000 / 0x100),
            $this->backColor % 0x100
        );
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $backColor);
        imagecolordeallocate($image, $backColor);

        if ($this->transparent) {
            imagecolortransparent($image, $backColor);
        }

        $foreColor = imagecolorallocate(
            $image,
            (int) ($this->foreColor % 0x1000000 / 0x10000),
            (int) ($this->foreColor % 0x10000 / 0x100),
            $this->foreColor % 0x100
        );

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $this->fontFile, $code);
        $w = $box[4] - $box[0] + $this->offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 20;
        $y = round($this->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int) (rand(26, 32) * $scale * 0.8);
            $angle = rand(-20, 20);
            $letter = $code[$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, imagecolorallocate($image, mt_rand(150,225), mt_rand(150,225), mt_rand(150,225)), $this->fontFile, $letter);
            $x = $box[2] + $this->offset;
        }
        //画干扰线
        $this->ext_line($image);
        //画干扰点
        $this->ext_point($image);

        ob_start();
        imagepng($image);
        imagedestroy($image);
        return ob_get_clean();
    }

    //干扰颜色
    private function ext_color($image) {
        return imagecolorallocate($image,rand(50, 180),rand(50, 180),rand(50, 180));
    }
    //生成干扰点
    private function ext_point($image) {
        for ($i=0; $i<$this->width*2; $i++) {
            imagesetpixel($image,rand(1,$this->width-1),rand(1,$this->height-1),$this->ext_color($image));
        }
    }
    //生成干扰线
    private function ext_line($image) {
        $len = 10;
        for ($i=0; $i<$len; $i++) {
            $x1 = rand(1,$this->width-1);
            $y1 = rand(1,$this->height-1);
            $x2 = rand(1,$this->width-1);
            $y2 = rand(1,$this->height-1);
            imageline($image,$x1,$y1,$x2,$y2,$this->ext_color($image));
        }
    }
}