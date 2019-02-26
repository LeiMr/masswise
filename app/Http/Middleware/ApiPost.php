<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\DB;
class ApiPost
{
    public function handle($request, Closure $next)
    {
        /**
         * 只允许post的方式访问接口
         *
         * @param  \Illuminate\Http\Request $request
         * @return mixed
         * */
        if (!$request->isMethod('post')) {
            $return = ["code" => "405", "data" => [], "msg" => "接口请求方式错误，请检查您的 GET / POST 方式"];
            return response()->json($return);
        }
        /**
         * 统一验证是否带有用户信息.
         *
         * @param  \Illuminate\Http\Request $request
         * @return mixed
         */
        if ($request->has('token')) {
            $sql_token = Db::table('token')->where('token', '=', $request->input('token'))->where('status', '=', 1)->first();
            if (!$sql_token || $sql_token->userid <= 0) {
                $return = ["code" => "2000", "data" => [], "msg" => "当前登录token无效，请重新登录"];
                return response()->json($return);
            } else {
                $request->offsetSet('UserId',$sql_token->userid);
            }
        }
        return $next($request);
    }
}
