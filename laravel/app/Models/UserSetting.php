<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema()
 */
class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "bank_token",
        "account_type",
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
     * TOKEN
     * @var string
     * @OA\Property(
     *  type="string",
     *  description="TOKEN",
     *  example="TOKEN"
     * )
     */
    private $bank_token;

    /**
     * 法人・個人 種別
     * @var integer
     * @OA\Property(
     *  type="integer",
     *  description="個人か法人か？ 個人 1 、 法人 2",
     *  example="1"
     * )
     */
    private $account_type;


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
