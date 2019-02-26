<?php
/**
 * 公用的方法  返回json数据，进行信息的提示
 * @param $status 状态
 * @param string $message 提示信息
 * @param array $data 返回数据
 */
function showMsg($status, $message = '', $data = array())
{
    $result = array(
        'status' => $status,
        'message' => $message,
        'data' => $data
    );
    exit(json_encode($result));
}

/**
 *  随机生成代码
 * @param array $data 返回数据
 */
function get_string($length, $type = 1)
{
    switch ($type) {
        case 1:
            $chars = '0123456789';
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            break;
        case 3:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqistuvwxyz0123456789';
            break;
        case 4:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqistuvwxyz0123456789+-=[]{}|\/<> !@#$%^&*:?.,';
            break;
    }
    $return = '';
    for ($i = 0; $i < $length; $i++) {
        $return .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $return;
}
/**
 *  curl get的形式访问。
 * @param array $data 返回数据
 */
function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
 }
/**
 *  curl post的形式访问。
 * @param array $data 返回数据
 */
 function httpPost($url,$array) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 500);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}
/**
 *  第三方短信接口
 * @param array $data 返回数据
 */
function sendMsg($mobile,$code){
    $url='http://120.78.138.165:9008';//系统接口地址
    $msg="【群智网络】验证码：".$code."，请在10分钟内使用，过时失效。";
    $content=urlencode(iconv('utf-8','GBK',$msg));
    $url=$url."/servlet/UserServiceAPI?method=sendSMS&extenno=&isLongSms=0&username=veth&password=".base64_encode(123456)."&smstype=0&mobile=".$mobile."&content=".$content;
    return file_get_contents($url);
}

/**
 * 判断是否为合法的身份证号码
 * @param $mobile
 * @return int
 */
function isCreditNo($vStr){
    $vCity = array(
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    );
    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
    if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);
    if ($vLength == 18) {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }
    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
    if ($vLength == 18) {
        $vSum = 0;
        for ($i = 17 ; $i >= 0 ; $i--) {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
        }
        if($vSum % 11 != 1) return false;
    }
    return true;
}