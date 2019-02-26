<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Member;
use Illuminate\Support\Facades\DB;
use Icharle\Wxtool\Wxtool;
class WeChatController extends Controller
{
    /**
     * 获取用户详细信息
     * @param Request $request
     */
    public function getUserInfo(Request $request)
    {
        if (!$request->has('code')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }
        $type=config('wxtool.wx_userinfo_type','2');
        if($type == '1'){
            if (!$request->has('encryptedData')) {
                $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'encryptedData'"];
                return response()->json($return);
            }
            if (!$request->has('iv')) {
                $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'iv'"];
                return response()->json($return);
            }
        }
        $a = new Wxtool();
        $code = $request->code;                                     //wx.login获取
        $encryptedData = $request->encryptedData;                   //wx.getUserInfo 获取
        $iv = $request->iv;                                         //wx.getUserInfo 获取
        if($type == '2') {
            $userinfo = $a->GetSessionKey($code);                   //获取用户openid 和 session_key
            if(isset($userinfo['code'])){
                $return = ["code" => "401", "data" => [], "msg" => "用户授权失败"];
                return response()->json($return);
            }

            $data   =   Db::table('wx_user')->where('openid',$userinfo['openid'])->first();
            if(!$data){
                $userid= Member::insertGetId(
                    [
                        'create_time' => time(),
                        'update_time' => time()
                    ]
                );
                if(!$userid){
                    $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
                    return response()->json($return);
                }
                $id = Db::table('wx_user')->insertGetId(
                    [
                        'openid' => $userinfo['openid'],
                        'userid' => $userid,
                        'create_time' => time(),
                        'update_time' => time()
                    ]
                );
                if(!$id){
                    $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
                    return response()->json($return);
                }

            }else{
                $userid= $data->id;
            }
            return response()->json($this->getAccessToken($userid));

        }
        if($type == '1') {
            $res= $a->GetSessionKey($code);                   //获取用户openid 和 session_key
            if(isset($res['code'])){
                $return = ["code" => "401", "data" => [], "msg" => "用户授权失败"];
                return response()->json($return);
            }
            $userinfo = $a->GetUserInfo($encryptedData, $iv);
            if(isset($userinfo['code'])){
                $return = ["code" => "402", "data" => [], "msg" => "用户信息解析失败"];
                return response()->json($return);
            }
            $userinfo=json_decode($userinfo, true);
            $data   =   Db::table('wx_user')->where('openid',$userinfo['openId'])->first();
            if(!$data){
                $userid= Member::insertGetId(
                    [
                        'create_time' => time(),
                        'update_time' => time()
                    ]
                );
                if(!$userid){
                    $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
                    return response()->json($return);
                }
                $id = Db::table('wx_user')->insertGetId(
                    [
                        'openid' => $userinfo['openId'],
                        'userid' => $userid,
                        'nickName' => $userinfo['nickName'],
                        'avatarUrl' => $userinfo['avatarUrl'],
                        'gender' => $userinfo['gender'],
                        'province' => $userinfo['province'],
                        'city' => $userinfo['city'],
                        'country' => $userinfo['country'],
                        'create_time' => time(),
                        'update_time' => time()
                    ]
                );
                if(!$id){
                    $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
                    return response()->json($return);
                }

            }else{
                $userid= $data->id;
            }
        }
        return response()->json($this->getAccessToken($userid));
    }
    /**
     * 根据用户id获取用户登陆凭证
     * @param String $token
     */
    private function getAccessToken($userid){
        Db::table('token')->where('userid',$userid)->update(['status'=>2]);
        $token=get_string(32,3);
        $id = Db::table('token')->insertGetId(
            [
                'userid' => $userid,
                'token' => $token,
                'status' => 1,
                'create_time' => time()
            ]
        );
        if(!$id){
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return $return;
        }
        $return = ["code" => "0", "data" => $token, "msg" => "SUCCESS"];
        return $return;
    }
}
