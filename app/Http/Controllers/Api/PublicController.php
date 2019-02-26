<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    //
    public function getSms($mobile)
    {
        if (!$mobile) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'mobile'"];
            return response()->json($return);
        }
        Db::table('mobile_check')->where('mobile', $mobile)->update(['status' => 2]);
        $code = get_string(6);
        $id = Db::table('mobile_check')->insertGetId(
            [
                'mobile' => $mobile,
                'check_code' => $code,
                'create_time' => time(),
                'status' => 1
            ]
        );
        if (!$id) {
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        $res = explode(';', sendMsg($mobile, $code));
        if($res[0]=='success'){
            $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
            return response()->json($return);
        }
        $return = ["code" => "10002", "data" => [], "msg" => "短信发送失败"];
        return response()->json($return);

    }
    //
    public function checkSms(Request $request)
    {
        if (!$request->has('mobile')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'mobile'"];
            return response()->json($return);
        }
        if (!$request->has('code')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'code'"];
            return response()->json($return);
        }
        $sql=Db::table('mobile_check')->where('mobile', $request->input('mobile'))->where('status',1)->first();
        if ($sql && $sql->check_code == $request->input('code')) {
            Db::table('mobile_check')->where('mobile', $request->input('mobile'))->where('status',1)->update(['status' => 2]);
            $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
            return response()->json($return);
        }
        $return = ["code" => "10003", "data" => [], "msg" => "短信验证失败"];
        return response()->json($return);

    }
}
