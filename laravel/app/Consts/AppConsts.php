<?php

namespace App\Consts;


/**
 * @see https://zenn.dev/aoi_avant/articles/19ca83d8ae67d7
 */
class AppConsts
{
    public const STATUS_CODE_SUCCESS = 0;
    public const STATUS_CODE_FALSE = -1;
    public const STATUS_OLD_VERSION = -2;   //バージョンチェックの時だけ使用する想定（2022/4/28）
    public const STATUS_HTTP_SUCCESS = 200;
    public const STATUS_HTTP_FALSE = 400;
    public const STATUS_HTTP_FALSE_NO_AUTH = 401;
    public const STATUS_HTTP_FALSE_ERROR = 403;
    public const STATUS_HTTP_FALSE_NOT_FOUND = 404;
    public const STATUS_HTTP_SYSTEM_ERROR = 500;
    public const JSON_CONTENT_TYPE = ['Content-Type' => 'application/json;charset=UTF-8'];


    public const API_URL_DOMAIN_SUNABAR = "https://api.sunabar.gmo-aozora.com/"; // SUNABAR
    public const API_URL_DOMAIN_PROD = "https://api.gmo-aozora.com/"; // 本番環境
    public const API_URL_DOMAIN_TEST = "https://stg-api.gmo-aozora.com/"; // 開発環境
    public const API_URL_DOMAIN = self::API_URL_DOMAIN_SUNABAR;

    public const API_ACCOUNT_TYPE_NONE = 0;
    public const API_ACCOUNT_TYPE_PERSONAL = 1;
    public const API_ACCOUNT_TYPE_CORP = 2;
    public const API_URL_TYPE_PERSONAL = "personal";
    public const API_URL_TYPE_CORP = "corporation";
    public const API_URL_VERSION = "v1";

    public const API_URL_PERSONAL_SUNABAR =
        self::API_URL_DOMAIN_SUNABAR . self::API_URL_TYPE_PERSONAL . "/" . self::API_URL_VERSION. "/";
    public const API_URL_CORP_SUNABAR =
        self::API_URL_DOMAIN_SUNABAR . self::API_URL_TYPE_CORP . "/" . self::API_URL_VERSION. "/";

    public const API_URL_PERSONAL_PROD =
        self::API_URL_DOMAIN_PROD . "ganb/" . self::API_URL_TYPE_PERSONAL . "/" . self::API_URL_VERSION. "/";
    public const API_URL_CORP_PROD =
        self::API_URL_DOMAIN_PROD . "ganb/" . self::API_URL_TYPE_CORP . "/" . self::API_URL_VERSION. "/";

    public const API_URL_PERSONAL_TEST =
        self::API_URL_DOMAIN_TEST . "ganb/" . self::API_URL_TYPE_PERSONAL . "/" . self::API_URL_VERSION. "/";
    public const API_URL_CORP_TEST =
        self::API_URL_DOMAIN_TEST . "ganb/" . self::API_URL_TYPE_CORP . "/" . self::API_URL_VERSION. "/";

    public const API_URL_PERSONAL = self::API_URL_PERSONAL_SUNABAR;
    public const API_URL_CORP = self::API_URL_CORP_SUNABAR;

    public const API_NFT_TYPE_NONE = 0;
    public const API_NFT_TYPE_BOARD_MEMBER = 1;
    public const API_NFT_TYPE_ETC = 2;

    // マスター法人口座
    public const ADMIN_BANK_CODE = "0310";//  	GMOあおぞらネット銀行 (固定)
    public const ADMIN_BRANCH_CODE = 101;//  	ｓｎｂｒ法人営業部(101)
//  public const ADMIN_BRANCH_CODE = 102;//  	ｓｎｂｒ法人第二営業部（102）
    public const ADMIN_ACCOUNT_TYPE_CODE = 1;// 科目コード（預金種別コード）円普通預金
    public const ADMIN_ACCOUNT_NUMBER = "0003940"; // 口座番号 for TEST
    public const ADMIN_ACCOUNT_NAME = "ｽﾅﾊﾞﾏｻﾋﾛ(ｶﾊﾝｽﾞｵﾝｺｳｻﾞ";// for TEST


}
