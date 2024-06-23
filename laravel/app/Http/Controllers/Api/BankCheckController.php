<?php

namespace App\Http\Controllers\Api;

use App\Consts\AppConsts;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\NftList;
use App\Models\UserSetting;
use App\Models\InvestHistory;
use App\Models\BankHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function response;

class BankCheckController extends Controller
{

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

        $lists = InvestHistory::select("*")->where('user_id', $login_user_id)->first();  //

//        Log::debug('LIST:', (array)$lists);
//        print_r($lists);

        if ($lists->status == 0) {
            $accountId = $lists->account_id;
            $appluNo = $lists->apply_no;
            $this->__getTransResult($login_user_id, $accountId, $appluNo);

        } else {
            print "すでに終了しています。";
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function month(Request $request)
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
//        $transferDesignatedDate = "2020-04-27";
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

        $json_response = $this->__getTransRequest($login_user_id, $requests);
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

    /**
     * NFT評価額合計
     * @param int $id
     * @return int
     */
    public function getNftTotal(int $id = 0)
    {
        $nft_lists = NftList::select("*")->where('user_id', $id)->get();  // ユーザー設定
        $total = 0;
        if ($nft_lists) {
            foreach ($nft_lists as $record) {
                $total += (int)sprintf("%d", $record->nft_valuation);
            }
        }
        return $total;
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


    private function __getTransResult($id, $accountId = "", $appluNo = "")
    {

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

        //リクエストデータ
        $url .= "transfer/request-result?accountId=" . $accountId . "&applyNo=" . $appluNo;
        if ($url != "") {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
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

        print_r($response);

        curl_close($curl);

        if ($err) {
            $json = ["cURL Error #:" . $err];
        } else {
            // echo $response;
            $json = json_decode($response, true);
        }

        return $json;

    }

    public function checkFurikomi()
    {
        $id = 1;
//        $result = Bank::getBankData($id);
        $result = Bank::getTrans($id);
        if (!isset($result["transactions"])) {
            print "ERROR 1";
            exit;
        }

        $BankHistory = BankHistory::select("*")->where('user_id', $id)->get();  // ユーザー設定

        $index = 0;
        if (count($BankHistory) < count($result["transactions"])) {
            $index++;
            $r = [];
            $i = [];
            foreach ($result["transactions"] as $record) {
                $flag = true;
                foreach ($BankHistory as $item) {
                    if (
                        ($record["remarks"] == $item->remarks) &&
                        ($record["balance"] == $item->balance) &&
                        ($record["itemKey"] == $item->itemKey)
                    ) {
                        // OK
                        $flag = false;
                        if (isset($r[$index])) {
                            unset($r[$index]);
                        }
                        if (isset($i[$index])) {
                            unset($i[$index]);
                        }
                    } else {
                        if ($flag) {
                            $r[$index] = $record;
                            $i[$index] = $item;

//                            $res = BankHistory::create([
//                                    "user_id" => $id,
//                                    "transactionDate" => $record["transactionDate"],
//                                    "valueDate" => $record["valueDate"],
//                                    "transactionType" => $record["transactionType"],
//                                    "amount" => $record["amount"],
//                                    "remarks" => $record["remarks"],
//                                    "balance" => $record["balance"],
//                                    "itemKey" => $record["itemKey"],
//                                ]
//                            );
//
//                            $res = NftList::create([
//                                    "user_id" => $id,
//                                    "nft_type" => 1,
//                                    "nft_title" => "2022年7月家賃",
//                                    "nft_address" => "",
//                                    "nft_url" => '#',
//                                    "img_url" => "https://daocar.llc/img/invest.png",
//                                    "nft_valuation" => $record["amount"],
//                                    "invest_month" => "202207",
////                            "start_str",
////                            "end_str",
//                                ]
//                            );
//                            print "確認！";

                        } else {
                            if (isset($r[$index])) {
                                unset($r[$index]);
                            }
                            if (isset($i[$index])) {
                                unset($i[$index]);
                            }
                        }
                    }
                }
            }
        }
        unset($record);

        if (isset($r) && is_array($r) && count($r) > 0) {
            foreach ($r as $record) {
                $res = BankHistory::create([
                        "user_id" => $id,
                        "transactionDate" => $record["transactionDate"],
                        "valueDate" => $record["valueDate"],
                        "transactionType" => $record["transactionType"],
                        "amount" => $record["amount"],
                        "remarks" => $record["remarks"],
                        "balance" => $record["balance"],
                        "itemKey" => $record["itemKey"],
                    ]
                );

                $res = NftList::create([
                        "user_id" => $id,
                        "nft_type" => 1,
                        "nft_title" => "2022年7月家賃",
                        "nft_address" => "",
                        "nft_url" => '#',
                        "img_url" => "https://daocar.llc/img/invest.png",
                        "nft_valuation" => $record["amount"],
                        "invest_month" => "202207",
//                            "start_str",
//                            "end_str",
                    ]
                );
                print "確認！";
            }
        }

//        print_r($BankHistory);
//        print_r($result);
        print "END\n";

    }


    private function __getTransferStatus($accountId = "")
    {

        //リクエストデータ
        $url = "transfer/status?accountId=" + $accountId + "&queryKeyClass=2";
    }


    /**
     * @param $id
     * @param $requests
     * @return false|string
     */
    private function __getTransRequest($id = "", $requests = [])
    {

        $url = "";
        $token = "";
        $accountId = "";

        $user_setting = UserSetting::select("*")->where('user_id', $id)->first();  // ユーザー設定
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

        $url .= "transfer/request";

        //振込指定日
//        $transferDesignatedDate = "2020-04-27";
        $transferDesignatedDate = $requests["transferDesignatedDate"];
        //振込金額
//        $transferAmount = "1000";
        $transferAmount = $requests["transferAmount"];
        //被仕向金融機関番号
//        $beneficiaryBankCode = "0310";
        $beneficiaryBankCode = $requests["beneficiaryBankCode"];
        //被仕向支店番号
//        $beneficiaryBranchCode = "102";
        $beneficiaryBranchCode = $requests["beneficiaryBranchCode"];
        //科目コード（預金種別コード）
//        $accountTypeCode = "1";
        $accountTypeCode = $requests["accountTypeCode"];
        //口座番号
//        $accountNumber = "0000406";
        $accountNumber = $requests["accountNumber"];
        //受取人名
//        $beneficiaryName = "ｽﾅﾊﾞｾｲｿﾞｳ(ｶ";
        $beneficiaryName = $requests["beneficiaryName"];

        // header
//        "accept" : "application/json;charset=UTF-8",
//    "Content-Type" : "application/json",
//    "x-access-token" : accesstoken,

        $send_json = sprintf('
        {
      "accountId": "%s",
      "transferDesignatedDate": "%s",
      "transfers": [
        {
          "transferAmount": "%s",
          "beneficiaryBankCode": "%s",
          "beneficiaryBranchCode": "%s",
          "accountTypeCode": "%s",
          "accountNumber": "%s",
          "beneficiaryName": "%s"
        }
      ]
    }', $accountId,
            $transferDesignatedDate,
            $transferAmount,
            $beneficiaryBankCode,
            $beneficiaryBranchCode,
            $accountTypeCode,
            $accountNumber,
            $beneficiaryName
        );

        $context = array(
            'http' => array(
                'method' => 'POST',
                'header' => implode("\r\n",
                    array(
                        'Content-Type: application/json',
                        "x-access-token: " . $token,
                        "accept: application/json;charset=UTF-8",
                    )
                ),
//                'content' => http_build_query($send_json)
                'content' => $send_json
            )
        );

        $json = file_get_contents($url, false, stream_context_create($context));

        print $json;

        return $json;
    }


}
