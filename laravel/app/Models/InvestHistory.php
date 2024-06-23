<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema()
 */
class InvestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "invest_month",
        "account_id",
        "result_code",
        "apply_no",
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
    private $invest_month;

    private $account_id;
    private $result_code;
    private $apply_no;

}
