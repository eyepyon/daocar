<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema()
 */
class NftList extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "nft_type",
        "nft_title",
        "nft_address",
        "nft_url",
        "img_url",
        "nft_valuation",
        "invest_month",
        "start_str",
        "end_str",
    ];


    /**
     * id
     * @var integer
     * @OA\Property(
     *  type="integer",
     *  description="ID",
     *  example="1"
     * )
     */
    private $id;

    /**
     * ユーザーid
     * @var integer
     * @OA\Property(
     *  type="integer",
     *  description="User ID",
     *  example="1"
     * )
     */
    private $user_id;

    /**
     * NFT種別
     * @var integer
     * @OA\Property(
     *  type="integer",
     *  description="NFTの種別 0.未定義 1.社員NFT 2.一般NFT",
     *  example="1"
     * )
     */
    private $nft_type;

    private $nft_title;

    /**
     * address
     * @var string
     * @OA\Property(
     *  type="string",
     *  description="nftオーナーのaddress",
     *  example="ADDRESS"
     * )
     */
    private $nft_address;

    /**
     * NFT URL
     * @var string
     * @OA\Property(
     *  type="string",
     *  description="nftのURL",
     *  example="URL"
     * )
     */
    private $nft_url;

    /**
     * サムネイル画像URL
     * @var string
     * @OA\Property(
     *  type="string",
     *  description="サムネイル画像URL",
     *  example="https://aaa.com/"
     * )
     */
    private $img_url;

    /**
     * NFT評価額
     * @var integer
     * @OA\Property(
     *  type="integer",
     *  description="NFTの評価額",
     *  example="1000000"
     * )
     */
    private $nft_valuation;

    private $invest_month;
    private $start_str;
    private $end_str;

    /**
     * NFT評価額合計
     * @param int $id
     * @return int
     */
    public function getNftTotal(int $id = 0)
    {
        $nft_lists = self::select("*")->where('user_id', $id)->get();  // ユーザー設定
        $total = 0;
        if ($nft_lists) {
            foreach ($nft_lists as $record) {
                $total += (int)sprintf("%d", $record->nft_valuation);
            }
        }
        return $total;
    }


//    /**
//     * 銀行名カナ
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="銀行名カナ",
//     *  example="ミツイスミトモモ"
//     * )
//     */
//    private $bank_name_kana;
//
//    /**
//     * 支店番号
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="支店番号",
//     *  example="123"
//     * )
//     */
//    private $bank_branch_number;
//
//    /**
//     * 支店名
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="支店名",
//     *  example="aaa支店"
//     * )
//     */
//    private $bank_branch_name;
//
//    /**
//     * 支店名カナ
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="支店名カナ",
//     *  example="aaaシテン"
//     * )
//     */
//    private $bank_branch_name_kana;
//
//    /**
//     * 銀行預金種目
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="銀行預金種目",
//     *  example="普通"
//     * )
//     */
//    private $bank_deposit_item;
//
//    /**
//     * 銀行口座番号
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="銀行口座番号",
//     *  example="5455457689"
//     * )
//     */
//    private $bank_account_number;

//    /**
//     * 依頼人コード
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="依頼人コード",
//     *  example="イライニン"
//     * )
//     */
//    private $client_code;
//
//    /**
//     * 銀行口座名義人
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="銀行口座名義人",
//     *  example="口座名義人"
//     * )
//     */
//    private $bank_account_name;
//
//    /**
//     * 銀行口座名義人カナ
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="銀行口座名義人カナ",
//     *  example="コウザメイギニン"
//     * )
//     */
//    private $bank_account_name_kana;
//
//    /**
//     * 銀行引落日
//     * @var string
//     * @OA\Property(
//     *  type="string",
//     *  description="銀行引落日",
//     *  example="2021/11/11"
//     * )
//     */
//    private $bank_withdrawal_date;

}
