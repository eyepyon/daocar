<?php

namespace App\Http\Controllers\Api;

use App\Consts\AppConsts;
use App\Http\Controllers\Controller;
use App\Models\NftList;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function response;

class TopController extends Controller
{
    //

    /**
     * @OA\Get(
     *      tags={"Top"},
     *      path="/api/Top",
     *      summary="トップ画面の表示。",
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
     *                          property="danka_yobina",
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

        $n = $this->getNftLists($login_user_id);
//      print_r($n);
        if($n){
//            Log::debug('NFT:', $n);
            $result_data["nft"] = (array)$n;
        }else{
            $result_data["nft"] = array();
        }
        $month = 0;
        $b = $this->getBankData($login_user_id,$month);
        if($b) {
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
        }else{
            $result_data["bank"] = array();
        }

        $result_data["month"] = $month;

        $nft_total = $this->getNftTotal($login_user_id);

        $balance = $this->getBalanceData($login_user_id);

        $yen = 0;
        $withdrawableAmount = 0;
        $previousDayBalance = 0;
        if(isset($balance["balance"])){
            $yen += $balance["balance"];
        }
        if(isset($balance["withdrawableAmount"])){
            $withdrawableAmount += $balance["withdrawableAmount"];
        }
        if(isset($balance["previousDayBalance"])){
            $previousDayBalance += $balance["previousDayBalance"];
        }

        $result_data["balance"] = array(
            "yen" => $yen,
            "yen_withdrawableAmount" => $withdrawableAmount,
            "yen_previousDayBalance" => $previousDayBalance,
            "nft" => $nft_total,
        );

//        $result_data["balance"] = array(
//            "yen" => "449200",
//            "nft" => "30000",
//        );
//      Log::debug('UserSetting:' . print_r($records, true));

        $res = $this->api_ress($status_code, $message, $result_data);
        return response()->json($res, $status_code_http, AppConsts::JSON_CONTENT_TYPE, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getNftLists(int $id = 0){

        $nft_lists = NftList::select("*")->where('user_id',$id)->get();  // ユーザー設定
        // debug
//        Log::debug('user_setting:', (array)$user_setting);
//        print_r($nft_lists);

        $return = array();
        if($nft_lists) {
            foreach ($nft_lists as $record){
                $link = "";
                Log::debug('nft_lists:', (array)$record);

                if($record->nft_type == AppConsts::API_NFT_TYPE_ETC){
                    if($record->nft_url){
                        $link = trim($record->nft_url);
                    }else{
                        $link = "https://opensea.io/".$record->nft_address;
                    }
                }
                $return[] = array(
                    "nft_type" => $record->nft_type,
                    "nft_address" => $record->nft_address,
                    "nft_url" => $link,
                    "img_url" => $record->img_url,
                    "nft_valuation" => $record->nft_valuation,
            );

            }


            return $return;
        }
        return $return;
    }

    /**
     * @param int $id
     * @return mixed|string[]
     */
    public function getBankData($id = 0,&$invest=0){

        $url = "";
        $token = "";

        $user_setting = UserSetting::select("*")->where('user_id',$id)->first();  // ユーザー設定
        // debug
//        Log::debug('user_setting:', (array)$user_setting);

         if($user_setting && isset($user_setting->account_type)){
            if($user_setting->account_type == AppConsts::API_ACCOUNT_TYPE_CORP){
                // 法人口座
                $url = AppConsts::API_URL_CORP;
            }else{
                //　個人口座
                $url = AppConsts::API_URL_PERSONAL;
            }
             $token = $user_setting->bank_token;
             $invest = $user_setting->invest;
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
    public function getBalanceData(int $id = 0){

        $url = "";
        $token = "";
        $accountId = "";

        $user_setting = UserSetting::select("*")->where('user_id',$id)->first();  // ユーザー設定
        // debug
//        Log::debug('user_setting:', (array)$user_setting);

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

        // debug
        Log::debug('url:', array($url));
        Log::debug('token:', [$token]);

        if ($url != "") {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url . "accounts/balances?accountId=".$accountId,
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

}
