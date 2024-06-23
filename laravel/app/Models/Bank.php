<?php

namespace App\Models;

use App\Consts\AppConsts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "amount",
        "interest_rate",
    ];

    /**
     * @param $id
     * @param $requests
     * @return false|string
     */
    public function getTransRequest($id = "", $requests = [])
    {

        $url = "";
        $token = "";
        $accountId = "";

        $user_setting = UserSetting::select("*")->where('user_id',$id)->first();  // ユーザー設定
        if($user_setting && isset($user_setting->account_type)){
            if($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP){
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            }else{
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
                'method'  => 'POST',
                'header'  => implode("\r\n",
                    array(
                        'Content-Type: application/json',
                        "x-access-token: ".$token,
                        "accept: application/json;charset=UTF-8",
                    )
                ),
//                'content' => http_build_query($send_json)
                'content' => $send_json
            )
        );

        $json = file_get_contents($url, false, stream_context_create($context));

//        print $json;

        return $json;
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
     * @param $id
     * @param $requests
     */
    private function getTransFee($id = 0, $requests = [])
    {
        $url = "";
        $token = "";
        $accountId = "";

        $user_setting = UserSetting::select("*")->where('user_id',$id)->first();  // ユーザー設定
        if($user_setting && isset($user_setting->account_type)){
            if($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP){
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            }else{
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
            $token = $user_setting->bank_token;
            $accountId = $user_setting->account_id;
        }

        $url .= "transfer/transferfee";

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
                'method'  => 'POST',
                'header'  => implode("\r\n",
                    array(
                        'Content-Type: application/json',
                        "x-access-token: ".$token,
                        "accept: application/json;charset=UTF-8",
                    )
                ),
//                'content' => http_build_query($send_json)
                'content' => $send_json
            )
        );

        $json = file_get_contents($url, false, stream_context_create($context));

//        print $json;

        return $json;

    }

    public function virtualList($id = 1){

        $user_setting = UserSetting::select("*")->where('user_id',$id)->first();  // ユーザー設定
        if($user_setting && isset($user_setting->account_type)){
            if($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP){
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            }else{
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
            $token = $user_setting->bank_token;
            $accountId = $user_setting->account_id;
        }

        $url .= "va/list";

        $body = array();
        // 振込入金口座API認証情報
//      $body["vaContractAuthKey"] = "";
        // 振込入金口座　種類コード 1=期限型 2=継続型
//        $body["vaTypeCode"] = 2;
        // 入金有無コード 1=入金あり 2=入金なし
//        $body["depositAmountExistCode"] = 1;
        // 振込入金口座名義カナ 半角文字
//        $body["vaHolderNameKana"] = "";
        // 振込入金口座IDリスト 上限3件まで array
//      $body["vaStatusCodeList"] = [];
        // 最終入金日From YYYY-MM-DD形式
        // 入金有無コードが未設定もしくは、「1=入金あり」が設定されている場合は指定可 それ以外はNULLを設定
//      $body["latestDepositDateFrom"] = "";
        // 最終入金日To YYYY-MM-DD形式
        // 入金有無コードが未設定もしくは、「1=入金あり」が設定されている場合は指定可 それ以外はNULLを設定
//      $body["latestDepositDateTo"] = "";
        // 振込入金口座発行日From YYYY-MM-DD形式
//      $body["vaIssueDateFrom"] = "";
        // 振込入金口座発行日To YYYY-MM-DD形式
//      $body["vaIssueDateTo"] = "";
        // 有効期限From YYYY-MM-DD形式
        // 振込入金口座 種類コードが未設定もしくは、「1＝期限型」が設定されている場合は指定可　それ以外はNULLを設定
//      $body["expireDateFrom"] = "";

        //　有効期限To　 YYYY-MM-DD形式
        // 振込入金口座 種類コードが未設定もしくは、「1＝期限型」が設定されている場合は指定可　それ以外はNULLを設定
//      $body["expireDateTo"] = "";
        // 入金口座ID 半角数字 口座一覧照会APIで取得できる口座IDを設定
        // 科目コードが以下の口座IDのみ受け付けます ・01=普通預金（有利息）02=普通預金（決済用）
//      $body["raId"] = "";
        // 次一覧キー
//      $body["nextItemKey"] = "";
        // ソート項目コード 1=有効期限日時 ・2=最終入金日 ・3=発行日時 ・4=最終入金金額
        $body["sortItemCode"] = 3;
        // ソート順コード ・1=昇順 ・2=降順
        $body["sortOrderCode"] = 2;
        // 振込入金口座ID 照会したい振込入金口座IDのリスト 上限500件まで
//      $body["vaIdList"] = [];

        $send_json = json_encode($body);

//      $accountId,

        $context = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => implode("\r\n",
                    array(
                        'Content-Type: application/json',
                        "x-access-token: ".$token,
                        "accept: application/json;charset=UTF-8",
                    )
                ),
//                'content' => http_build_query($send_json)
                'content' => $send_json
            )
        );

        $json = file_get_contents($url, false, stream_context_create($context));
        return $json;
    }

    /**
     * @param $id
     * @return false|string
     */
    public function makeVirtual($id = 1){

        $user_setting = UserSetting::select("*")->where('user_id',$id)->first();  // ユーザー設定
        if($user_setting && isset($user_setting->account_type)){
            if($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP){
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            }else{
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
            $token = $user_setting->bank_token;
            $accountId = $user_setting->account_id;
        }

        $url .= "va/issue";
        $body = array();

        // 振込入金口座　種類コード 1=期限型 2=継続型　必須
        $body["vaTypeCode"] = "2";
        // 発行口座数 必須
        $body["issueRequestCount"] = "1";
        // 入金口座ID 半角数字 口座一覧照会APIで取得できる口座IDを設定
        // 科目コードが以下の口座IDのみ受け付けます ・01=普通預金（有利息）02=普通預金（決済用）
        $body["raId"] = $accountId;
        // 振込入金口座API認証情報
        // 銀行契約の方はNULLを設定
        // 提携企業の方が、契約された顧客の発行を依頼される場合は必須
        $body["vaContractAuthKey"] = null;
        // 追加名義カナ
        $body["vaHolderNameKana"] = "ﾊﾂｶｿﾝ";
        // 追加名義位置 追加名義カナを口座名義の前につけるか後ろにつけるかの指定
        //・1=通常（後ろにつける）
        //・2=前につける
        $body["vaHolderNamePos"] = 2;

        $send_json = json_encode($body);

//      $accountId,

        $context = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => implode("\r\n",
                    array(
                        'Content-Type: application/json',
                        "x-access-token: ".$token,
                        "accept: application/json;charset=UTF-8",
                    )
                ),
//                'content' => http_build_query($send_json)
                'content' => $send_json
            )
        );

        $json = file_get_contents($url, false, stream_context_create($context));
        preg_match("/[0-9]{3}/", $http_response_header[0], $stcode);
        //ステータスコードによる分岐
        if((int)$stcode[0] >= 100 && (int)$stcode[0] <= 199) {
            Log::debug('ERROR:', $http_response_header);
        }
        return $json;

    }

    public function makeNewAccount($id=0){

        $url = "";
        $token = "";
        $accountId = "";

        $user_setting = UserSetting::select("*")->where('user_id',$id)->first();  // ユーザー設定
        if($user_setting && isset($user_setting->account_type)){
            if($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP){
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            }else{
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
            $token = $user_setting->bank_token;
            $accountId = $user_setting->account_id;
        }

        $url .= "va/status-change";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{
\"vaContractAuthKey\" : null,
\"vaStatusChangeCode\" : \"1\",
\"vaIdList\" : [
 {
  \"vaId\" : \"5021099622\"
 },
 {
  \"vaId\" : \"5021099636\"
 },
 {
  \"vaId\" : \"5021099645\"
 },
 {
  \"vaId\" : \"5021099657\"
 },
 {
  \"vaId\" : \"5021099662\"
 }
]
}
",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json;charset=UTF-8",
                "content-type: application/json",
                "x-access-token: ".$token
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }

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

//        $start_date = date("Y-m-d",strtotime(date("Y-m-d" , "-3 month")));;
        $start_date = "2022-04-01";
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


}
