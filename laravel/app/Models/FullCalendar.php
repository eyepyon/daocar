<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FullCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "group_id",
        "all_day",
        "start_date",
        "end_date",
        "start_str",
        "end_str",
        "start_str",
        "title",
        "url",
        "class_names",
        "editable",
        "start_editable",
        "duration_editable",
        "resource_editable",
        "display",
        "overlap",
        "constraint",
        "background_color",
        "border_color",
        "text_color",
        "description",
        "extended_props",
        "delete_flg",
        "deleted_at",
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
     * kashitsuke
     * @var integer
     * @OA\Property(
     *  type="integer",
     *  description="kasithuke",
     *  example="1000"
     * )
     */
    private $group_id;
    private $all_day;
    private $start_date;
    private $end_date;
    private $start_str;
    private $end_str;
    private $title;
    private $url;
    private $class_names;
    private $editable;
    private $start_editable;
    private $duration_editable;
    private $resource_editable;
    private $display;
    private $overlap;
    private $constraint;
    private $background_color;
    private $border_color;
    private $text_color;
    private $description;
    private $extended_props;
    private $source;
//    private $created_at;
//    private $updated_at;
    private $delete_flg;
    private $deleted_at;
}
