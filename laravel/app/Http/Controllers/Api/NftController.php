<?php

namespace App\Http\Controllers\Api;

use App\Consts\AppConsts;
use App\Http\Controllers\Controller;
use App\Models\NftList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function response;

class NftController extends Controller
{
    //

    /**
     * @OA\Get(
     *      tags={"Nft"},
     *      path="/api/Nft",
     *      summary="NFT画面の表示。",
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
     *                          description="「」という単語の変更する。",
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
        if ($nft_lists) {
            foreach ($nft_lists as $record) {
                $link = "";
                Log::debug('nft_lists:', (array)$record);

                if ($record->nft_type == AppConsts::API_NFT_TYPE_ETC) {
                    if ($record->nft_url) {
                        $link = trim($record->nft_url);
                    } else {
                        $link = "https://opensea.io/" . $record->nft_address;
                    }
                }

                $return[] = array(
                    "nft_type" => $record->nft_type,
                    "nft_title" => $record->nft_title,
                    "nft_address" => $record->nft_address,
                    "nft_url" => $link,
                    "img_url" => $record->img_url,
                    "nft_valuation" => $record->nft_valuation,
                    "invest_month" => $record->invest_month,
                    "start_str" => $record->start_str,
                    "end_str" => $record->end_str,
                );
            }
            return $return;
        }
        return $return;
    }

}
