<?php

namespace App\Http\Controllers\Api;

use App\Consts\AppConsts;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\NftList;
use App\Models\UserSetting;
use App\Models\InvestHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function response;

class BankController extends Controller
{
    //

    /**
     * @OA\Get(
     *      tags={"Bank"},
     *      path="/api/Bank",
     *      summary="銀行画面の表示。",
     *      @OA\Response(
     *          response=200,
     *          description="処理成功時　status_code=0, message="""",  result={}
     *                      処理失敗時　status_code=-1, message=""エラーメッセージや警告文"", result={}
     *                      処理警告時(処理は正常に進めるがメッセージを表示する。)　status_code=1, message=""警告文"", result={表示する内容}",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status_code",
     *                  type="integer",
     *                  description="処理が成功したか失敗したか。1：警告、0：成功、-1：失敗",
     *                  example="0"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="エラーメッセージや警告文。処理成功時は空っぽ",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="result",
     *                  type="object",
     *                  description="処理の結果",
     *                  @OA\Property(
     *                      property="user_setting",
     *                      type="object",
     *                      description="ユーザー設定の値。",
     *                      @OA\Property(
     *                          property="",
     *                          type="string",
     *                          description="",
     *                          example="",
     *                      )
     *                  ),
     *              )
     *          )
     *      ),
     * @OA\Response(
     *          response=401,
     *          description="セッションタイムアウト。「セッションタイムです。\n再ログインしてください。」と表示する。"
     *      ),
     * @OA\Response(
     *          response=404,
     *          description="not found　深く考えられていないので今後変更します。"
     *      )
     * )
     */
    public function index(Request $request)
    {

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

        $b = $this->getBankData($login_user_id);
        if ($b) {
            Log::debug('BANK:', $b);

//        print_r($bank);
            $bank = $b["accounts"][0];

            $result_data["bank"] = array(
                "accountId" => $bank["accountId"],
                "branchCode" => $bank["branchCode"],
                "branchName" => $bank["branchName"],
                "accountTypeCode" => $bank["accountTypeCode"],
                "accountNumber" => $bank["accountNumber"],
                "primaryAccountCode" => $bank["primaryAccountCode"],
                "primaryAccountCodeName" => $bank["primaryAccountCodeName"],
                "accountName" => $bank["accountName"],
                "accountNameKana" => $bank["accountNameKana"],
                "currencyCode" => $bank["currencyCode"],
                "currencyName" => $bank["currencyName"],
                "transferLimitAmount" => $bank["transferLimitAmount"],
            );
        } else {
            $result_data["bank"] = array();
        }

        $nft_total = NftList::getNftTotal($login_user_id);

        $balance = $this->getBalanceData($login_user_id);
//        print_r($balance);

        /**
         *     [accountId] => 302010004268
         *     [accountTypeCode] => 01
         *    [accountTypeName] => 普通預金（有利息）
         * [balance] => 93537
         * [baseDate] => 2022-05-29
         * [baseTime] => 12:26:43+09:00
         * [withdrawableAmount] => 93537
         * [previousDayBalance] => 93537
         * [previousMonthBalance] => 0
         * [currencyCode] => JPY
         * [currencyName] => 日本円
         */

        $yen = 0;
        $withdrawableAmount = 0;
        $previousDayBalance = 0;
        if (isset($balance["balance"])) {
            $yen += $balance["balance"];
        }
        if (isset($balance["withdrawableAmount"])) {
            $withdrawableAmount += $balance["withdrawableAmount"];
        }
        if (isset($balance["previousDayBalance"])) {
            $previousDayBalance += $balance["previousDayBalance"];
        }

        $result_data["balance"] = array(
            "yen" => $yen,
            "yen_withdrawableAmount" => $withdrawableAmount,
            "yen_previousDayBalance" => $previousDayBalance,
            "nft" => $nft_total,
        );
//      Log::debug('UserSetting:' . print_r($records, true));

        $transactions = $this->getTrans($login_user_id);
        $result_data["transactions"] = $transactions;

        $res = $this->api_ress($status_code, $message, $result_data);
        return response()->json($res, $status_code_http, AppConsts::JSON_CONTENT_TYPE, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function check(Request $request)
    {

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

        $transferAmount = 10000; // 振込金額

        $requests = [];
        //振込指定日
        $requests["transferDesignatedDate"] = date("Y-m-d");// 当日
        //振込金額
//        $transferAmount = "1000";
        $requests["transferAmount"] = $transferAmount;
        //被仕向金融機関番号
//        $beneficiaryBankCode = "0310";
        $requests["beneficiaryBankCode"] = AppConsts::ADMIN_BANK_CODE;
        //被仕向支店番号
//        $beneficiaryBranchCode = "102";
        $requests["beneficiaryBranchCode"] = AppConsts::ADMIN_BRANCH_CODE;
        //科目コード（預金種別コード）
//        $accountTypeCode = "1";
        $requests["accountTypeCode"] = AppConsts::ADMIN_ACCOUNT_TYPE_CODE;
        //口座番号
//        $accountNumber = "0000406";
        $requests["accountNumber"] = AppConsts::ADMIN_ACCOUNT_NUMBER;
        //受取人名
//        $beneficiaryName = "ｽﾅﾊﾞｾｲｿﾞｳ(ｶ";
        $requests["beneficiaryName"] = AppConsts::ADMIN_ACCOUNT_NAME;

        Bank::getTransFee($login_user_id, $requests);

        // { "accountId": "302010004268" ,"baseDate": "2022-05-29" ,"baseTime": "14:21:35+09:00" ,"totalFee": "0" ,"transferFeeDetails" : [ { "itemId": "1" ,"transferFee": "0" } ] }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function invest(Request $request)
    {

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

        $transferAmount = 180000; // 振込金額

        $requests = [];
        //振込指定日
        $requests["transferDesignatedDate"] = date("Y-m-d");// 当日
        //振込金額
//        $transferAmount = "1000";
        $requests["transferAmount"] = $transferAmount;
        //被仕向金融機関番号
//        $beneficiaryBankCode = "0310";
        $requests["beneficiaryBankCode"] = AppConsts::ADMIN_BANK_CODE;
        //被仕向支店番号
//        $beneficiaryBranchCode = "102";
        $requests["beneficiaryBranchCode"] = AppConsts::ADMIN_BRANCH_CODE;
        //科目コード（預金種別コード）
//        $accountTypeCode = "1";
        $requests["accountTypeCode"] = AppConsts::ADMIN_ACCOUNT_TYPE_CODE;
        //口座番号
//        $accountNumber = "0000406";
        $requests["accountNumber"] = AppConsts::ADMIN_ACCOUNT_NUMBER;
        //受取人名
//        $beneficiaryName = "ｽﾅﾊﾞｾｲｿﾞｳ(ｶ";
        $requests["beneficiaryName"] = AppConsts::ADMIN_ACCOUNT_NAME;

        $json_response = Bank::getTransRequest($login_user_id, $requests);
        $response = json_decode($json_response, true);

        $lastmonth = date("Ym", strtotime(date('+1 month')));
        $res = InvestHistory::create([
                "user_id" => $login_user_id,
                "invest_month" => $lastmonth,
                "account_id" => $response["accountId"],
                "result_code" => $response["resultCode"],
                "apply_no" => $response["applyNo"],
            ]
        );
        if ($res) {
//            print "OK";
            $res = NftList::create([
                    "user_id" => $login_user_id,
                    "nft_type" => AppConsts::API_NFT_TYPE_BOARD_MEMBER,
                    "nft_address" => '',
                    "nft_url" => '#',
                    "img_url" => "https://daocar.llc/img/invest.png",
                    "nft_valuation" => $transferAmount,
                    "invest_month" => $lastmonth,
                ]
            );
            if ($res) {
                print "OK";
            }
        } else {
            print"NG";
        }
// { "accountId": "302010004268" ,"resultCode": "2" ,"applyNo": "2022052900000001" }

    }

    public function list(Request $request)
    {

        $status_code = 0;
        $status_code_http = 200;
        $message = "";
        $result_data = array();
        $res = $this->api_ress($status_code, $message, $result_data);
        return response()->json($res, $status_code_http, AppConsts::JSON_CONTENT_TYPE, JSON_UNESCAPED_UNICODE);
    }

    public function create(Request $request)
    {

        $status_code = 0;
        $status_code_http = 200;
        $message = "";
        $result_data = array();

        $res = $this->api_ress($status_code, $message, $result_data);
        return response()->json($res, $status_code_http, AppConsts::JSON_CONTENT_TYPE, JSON_UNESCAPED_UNICODE);

    }


    /**
     * @param int $id
     * @return mixed|string[]
     */
    public function getBankData($id = 0)
    {

        $url = "";
        $token = "";

        $user_setting = UserSetting::select("*")->where('user_id', $id)->first();  // ユーザー設定
        // debug
//        Log::debug('user_setting:', (array)$user_setting);

        if ($user_setting && isset($user_setting->account_type)) {
            if ($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP) {
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            } else {
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
            $token = $user_setting->bank_token;
        }

        // debug
        Log::debug('url:', array($url));
        Log::debug('token:', [$token]);

        if ($url != "") {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url . "accounts",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json;charset=UTF-8",
                    "x-access-token: " . $token
                ),
            ));
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $json = ["cURL Error #:" . $err];
        } else {
            // echo $response;
            $json = json_decode($response, true);
        }

        return $json;
    }

    /**
     * @param int $id
     * @return mixed|string[]
     */
    public function getBalanceData(int $id = 0)
    {

        $url = "";
        $token = "";
        $accountId = "";

        $user_setting = UserSetting::select("*")->where('user_id', $id)->first();  // ユーザー設定
        // debug
//        Log::debug('user_setting:', (array)$user_setting);

        if ($user_setting && isset($user_setting->account_type)) {
            if ($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP) {
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            } else {
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
            $token = $user_setting->bank_token;
            $accountId = $user_setting->account_id;
        }

        // debug
        Log::debug('url:', array($url));
        Log::debug('token:', [$token]);

        if ($url != "") {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url . "accounts/balances?accountId=" . $accountId,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json;charset=UTF-8",
                    "x-access-token: " . $token
                ),
            ));
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $json = ["cURL Error #:" . $err];
        } else {
            // echo $response;
            $json = json_decode($response, true);
            return $json["balances"][0];
        }

        return $json;
    }

    public function virtualList(){
       $result = Bank::virtualList();
       print_r($result);
    }

    public function makeVirtual(){
        $result = Bank::makeVirtual();
        print_r($result);
    }

    /**
     * @param $id
     * @return mixed|string[]
     */
    public function getTrans(int $id = 0)
    {

        $url = "";
        $token = "";
        $accountId = "";

        $user_setting = UserSetting::select("*")->where('user_id', $id)->first();  // ユーザー設定
        // debug
//        Log::debug('user_setting:', (array)$user_setting);

        if ($user_setting && isset($user_setting->account_type)) {
            if ($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP) {
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            } else {
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
            $token = $user_setting->bank_token;
            $accountId = $user_setting->account_id;
        }

        // debug
        Log::debug('url:', array($url));
        Log::debug('token:', [$token]);

        $start_date = date("Y-m-d",strtotime(date("Y-m-d" , "-3 month")));;
        Log::debug('start_date:', [$start_date]);
        // ３ヶ月前まで
        $curl_url = $url . "accounts/transactions?accountId=" . $accountId;
        $curl_url .= "&dateFrom=" . urlencode($start_date);

//        $curl_url .= "&dateTo=2018-12-31";

        if ($url != "") {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "accept: application/json;charset=UTF-8",
                    "x-access-token: " . $token
                ),
            ));
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $json = ["cURL Error #:" . $err];
        } else {
            // echo $response;
            $json = json_decode($response, true);
//            return $json["balances"][0];
        }

        return $json;
    }

    private function __getTransResult($accountId = "", $appluNo = "")
    {

        //リクエストデータ
        $url = "transfer/request-result?accountId" . $accountId . "&applyNo=" . $appluNo;
    }

    private function __getTransferStatus($accountId = "")
    {

        //リクエストデータ
        $url = "transfer/status?accountId=" + $accountId + "&queryKeyClass=2";
    }


}
