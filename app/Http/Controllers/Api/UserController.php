<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Model\Member;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class UserController extends Controller
{
    //
    public function details(Request $request)
    {
        $id = $request->input('id');
        $data = Member::find($id);
        if (!$data) {
            $return = ["code" => "10001", "data" => [], "msg" => "用户不存在"];
            return response()->json($return);
        }
        $return = ["code" => "0", "data" => $data, "msg" => "SUCCESS"];
        return response()->json($return);
    }

    public function mineDetails(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }
        $data = Member::find($request->input('UserId'));
        $return = ["code" => "0", "data" => $data, "msg" => "SUCCESS"];
        return response()->json($return);
    }

    public function authentication(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }
        //参数验证
        if (!$request->input('idCardNo')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'idCardNo'"];
            return response()->json($return);
        }
        if (!$request->input('name')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'name'"];
            return response()->json($return);
        }
        if (!$request->input('mobile')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'mobile'"];
            return response()->json($return);
        }
        if (!isCreditNo($request->input('idCardNo'))) {
            $return = ["code" => "601", "data" => [], "msg" => "格式错误,参数.'idCardNo'"];
            return response()->json($return);
        }
        $sql_auth_mobile = Db::table('authentication')->where('mobile', $request->input('mobile'))->first();
        if ($sql_auth_mobile) {
            $return = ["code" => "2003", "data" => [], "msg" => "手机号码已绑定其他账号"];
            return response()->json($return);
        }
        $sql_auth_userid = Db::table('authentication')->where('userid', $request->input('UserId'))->first();

        if ($sql_auth_userid) {
            $return = ["code" => "2004", "data" => [], "msg" => "用户已实名验证，请勿重复提交"];
            return response()->json($return);
        }
        $sql_auth_idCardNo = Db::table('authentication')->where('idCardNo', $request->input('idCardNo'))->first();

        if ($sql_auth_idCardNo) {
            $return = ["code" => "2005", "data" => [], "msg" => "身份证号码已绑定其他账号"];
            return response()->json($return);
        }
        $id = Db::table('authentication')->insertGetId(
            [
                'userid' => $request->input('UserId'),
                'name' => $request->input('name'),
                'idCardNo' => $request->input('idCardNo'),
                'mobile' => $request->input('mobile'),
                'create_time' => time(),
                'update_time' => time()
            ]
        );
        if (!$id) {
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        Member::where('id', $request->input('UserId'))
            ->update(
                [
                    'quotaTotal' => DB::raw('quotaTotal + 200'),
                    'quotaConsumable' => DB::raw('quotaConsumable + 200'),
                    'update_time' => time()
                ]
            );
        $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
        return $return;
    }

    public function checkAuthentication(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }
        $sql_auth_userid = Db::table('authentication')->where('userid', $request->input('UserId'))->first();

        if ($sql_auth_userid) {
            $return = ["code" => "0", "data" => ['status'=>1], "msg" => "用户已实名验证"];
            return response()->json($return);
        }
        $return = ["code" => "0", "data" => ['status'=>2], "msg" => "SUCCESS"];
        return response()->json($return);
    }
    public function addressList(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }
        $data = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->when($request->input('offset'), function ($query, $role) {
                return $query->offset($role);
            })
            ->when($request->input('limit'), function ($query, $role) {
                return $query->limit($role);
            })
            ->get()
           ->toArray();
        foreach($data as $key =>$val){
            $data[$key]->create_time=date('Y-m-d H:i:s',$val->create_time);
            $data[$key]->update_time=date('Y-m-d H:i:s',$val->update_time);
        }
        $length= Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->count();
        $return = ["code" => "0", "data" => $data, "msg" => "SUCCESS","total"=>$length];
        return response()->json($return);
    }

    public function addressAdd(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }
        //参数验证
        if (!$request->input('province')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'province'"];
            return response()->json($return);
        }
        if (!$request->input('city')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'city'"];
            return response()->json($return);
        }
        if (!$request->input('county')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'county'"];
            return response()->json($return);
        }
        if (!$request->input('address')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'address'"];
            return response()->json($return);
        }
        if (!$request->input('name')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'name'"];
            return response()->json($return);
        }
        if (!$request->input('mobile')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'mobile'"];
            return response()->json($return);
        }
        $data = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->where('province', $request->input('province'))
            ->where('city', $request->input('city'))
            ->where('county', $request->input('county'))
            ->where('address', $request->input('address'))
            ->where('name', $request->input('name'))
            ->where('mobile', $request->input('mobile'))
            ->first();
        if ($data) {
            $return = ["code" => "2009", "data" => [], "msg" => "拥有完全一致的收货信息，无需再次新增"];
            return response()->json($return);
        }
        $data = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->first();
        if (!$data) {
            $defult = 1;
        } else {
            $defult = 0;
        }
        $id = Db::table('member_address')->insertGetId(
            [
                'userid' => $request->input('UserId'),
                'province' => $request->input('province'),
                'city' => $request->input('city'),
                'county' => $request->input('county'),
                'address' => $request->input('address'),
                'name' => $request->input('name'),
                'mobile' => $request->input('mobile'),
                'default' => $defult,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time()
            ]
        );
        if (!$id) {
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
        return response()->json($return);
    }

    public function addressUpdate(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }

        //参数验证
        if (!$request->input('id')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'id'"];
            return response()->json($return);
        }
        if (!$request->input('province')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'province'"];
            return response()->json($return);
        }
        if (!$request->input('city')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'city'"];
            return response()->json($return);
        }
        if (!$request->input('county')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'county'"];
            return response()->json($return);
        }
        if (!$request->input('address')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'address'"];
            return response()->json($return);
        }
        if (!$request->input('name')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'name'"];
            return response()->json($return);
        }
        if (!$request->input('mobile')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'mobile'"];
            return response()->json($return);
        }
        $data = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->where('province', $request->input('province'))
            ->where('city', $request->input('city'))
            ->where('county', $request->input('county'))
            ->where('address', $request->input('address'))
            ->where('name', $request->input('name'))
            ->where('mobile', $request->input('mobile'))
            ->first();
        if($data){
            if($data->id != $request->input('id')){
            $return = ["code" => "2009", "data" => [], "msg" => "拥有完全一致的收货信息"];
            return response()->json($return);
            }else{
                $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
                return response()->json($return);
            }
        }
        $id = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('id', $request->input('id'))
            ->update(
            [
                'userid' => $request->input('UserId'),
                'province' => $request->input('province'),
                'city' => $request->input('city'),
                'county' => $request->input('county'),
                'address' => $request->input('address'),
                'name' => $request->input('name'),
                'mobile' => $request->input('mobile'),
                'update_time' => time()
            ]
        );
        if (!$id) {
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
        return response()->json($return);
    }
    public function addressDel(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }

        //参数验证
        if (!$request->input('id')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'id'"];
            return response()->json($return);
        }

        $data = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->where('id', $request->input('id'))
            ->first();
        if(!$data){
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        $id = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('id', $request->input('id'))
            ->update(
                [
                    'status' => -1,
                    'default' => 0,
                ]
            );
        if (!$id) {
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        if($data->default == '1'){
            $sql_id=Db::table('member_address')
                ->where('userid', $request->input('UserId'))
                ->where('status', 1)
                ->orderByDesc('create_time')
                ->first();
            if($sql_id){
                Db::table('member_address')
                    ->where('userid', $request->input('UserId'))
                    ->where('id', $sql_id->id)
                    ->update(
                        [
                            'default' => 1,
                        ]
                    );
            }
        }
        $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
        return response()->json($return);
    }
    public function addressDefault(Request $request)
    {
        if (!$request->input('UserId')) {
            $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
            return response()->json($return);
        }

        //参数验证
        if (!$request->input('id')) {
            $return = ["code" => "600", "data" => [], "msg" => "缺少参数.'id'"];
            return response()->json($return);
        }

        $data = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->where('id', $request->input('id'))
            ->first();
        if(!$data){
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        if($data->default == '1'){
            $return = ["code" => "0", "data" => [], "msg" => "SUCCESS2"];
            return response()->json($return);
        }
        $id = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('status', 1)
            ->update(
                [
                    'default' => 0,
                ]
            );
        if (!$id) {
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        $id = Db::table('member_address')
            ->where('userid', $request->input('UserId'))
            ->where('id', $request->input('id'))
            ->where('status', 1)
            ->update(
                [
                    'default' => 1,
                ]
            );
        if (!$id) {
            $return = ["code" => "-1", "data" => [], "msg" => "服务器内部错误"];
            return response()->json($return);
        }
        $return = ["code" => "0", "data" => [], "msg" => "SUCCESS"];
        return response()->json($return);
    }

}
