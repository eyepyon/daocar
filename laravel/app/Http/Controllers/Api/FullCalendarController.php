<?php

namespace App\Http\Controllers\Api;

use App\Consts\AppConsts;
use App\Http\Controllers\Controller;
use App\Models\FullCalendar;
use Illuminate\Http\Request;

class FullCalendarController extends Controller
{
    public function index(Request $request){

        $status_code = 0;
        $status_code_http = 200;
        $message = "";
        $result_data = array();

        $login_user_id = 0;

        //ユーザーIDを取得
        if ($request->session()->has('user_id')) {
            //ログインユーザーのIDをセッションから取得
            $login_user_id = $request->session()->get('user_id');
        }
        $login_user_id = 1; //仮・テスト用。セッション等から取得する想定

        if ($login_user_id < 1) {
            //権限がない場合の処理。
            $status_code = -1;
            $status_code_http = 401;
            $message = 'ログインが必要です。';
            $result = array();
            $res = $this->api_ress($status_code, $message, $result);
            return response()->json($res, $status_code_http, AppConsts::JSON_CONTENT_TYPE, JSON_UNESCAPED_UNICODE);
        }

        $lists = FullCalendar::select("*")->where('user_id', $login_user_id)->first();  //

//        Log::debug('LIST:', (array)$lists);
//        print_r($lists);

        if($lists->status == 0){
            $accountId = $lists->account_id;
            $appluNo = $lists->apply_no;
            $this->__getTransResult($login_user_id,$accountId , $appluNo );

        }else{
            print "すでに終了しています。";
        }

    }



}
