<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema()
 */
class BankHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "transactionDate",
        "valueDate",
        "transactionType",
        "amount",
        "remarks",
        "balance",
        "itemKey",
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
     * invest_month
     * @var string
     * @OA\Property(
     *  type="string",
     *  description="YYYYMM",
     *  example="YYYYMM"
     * )
     */
    private $transactionDate;
    private $valueDate;
    private $transactionType;
    private $amount;
    private $remarks;
    private $balance;
    private $itemKey;


}
