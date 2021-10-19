<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use Minho\Captcha\CaptchaBuilder;

use EasySwoole\VerifyCode\Conf;
use EasySwoole\VerifyCode\VerifyCode;

class Captcha extends Controller
{


    //获取验证吗
    public function CaptchaImg($height=200,$width=400){
        $Conf = new Conf();
        $Conf->setBackColor('#3A5FCD');
        $Conf->setBackColor('CCC');
        $Conf->setBackColor([30, 144, 255]);
        $Conf->setUseCurve();
        $Conf->setUseNoise();
        // 设置验证码图片的宽度
        $Conf->setImageWidth($width);
// 设置验证码图片的高度
        $Conf->setImageHeight($height);
// 设置生成字体大小
        $Conf->setFontSize(30);
// 设置生成验证码位数
        $Conf->setLength(4);

        $VCode = new \EasySwoole\VerifyCode\VerifyCode($Conf);

        $DrawCodeResult = $VCode->DrawCode();
        $codeStr = $DrawCodeResult->getImageCode();
        var_dump($codeStr);
        $this->response()->withHeader('Content-Type',"image/png");
        $this->response()->write($DrawCodeResult->getImageByte());

    }
    //base64

    public function getBase64(){
        $conf = new Conf();
        $code = new \EasySwoole\VerifyCode\VerifyCode($conf);

        //生成验证吗
        $drawcode = $code->DrawCode();
        //

        $codeStr = $drawcode->getImageCode();

        var_dump($codeStr);
        //想客户端输出验证码 base64 编码，前端可以用来生成图片

        $this->response()->write($drawcode->getImageBase64());
    }

    // function test
    public function test(){

        $sessionId = $this->request()->getAttribute('easy_session');

        var_dump($sessionId);
        $this->response()->write("test");
    }
}
