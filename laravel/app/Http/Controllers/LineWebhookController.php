<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

//use Alle_AI\Anthropic\AnthropicAPI;

//use App\Services\LineBotService as LINEBot;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use LINE\LINEBot\Event\MessageEvent\FileMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
use OpenAI\Client;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\DB;
use App\Models\FileData;
use App\Models\ImageData;
use App\Models\User;

//use Google\Cloud\Vision\V1\Feature\Type;
//use Google\Cloud\Vision\V1\ImageAnnotatorClient;
//use Google\Cloud\Vision\V1\Likelihood;
//use Google\Cloud\Vision\V1\Position;
use Intervention\Image\Facades\Image;
use Mtownsend\RemoveBg\RemoveBg;


/**
 * Class LineWebhookController
 * @package App\Http\Controllers
 *
 */
class LineWebhookController extends Controller
{
    /**
     * @param Request $request
     * @return never|void
     */
    public function message(Request $request)
    {
        $lineUserId = "";

        // 認証情報取得
        $httpClient = new CurlHTTPClient(config('services.line.message.channel_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('services.line.message.channel_secret')]);

        $signature = $request->header(HTTPHeader::LINE_SIGNATURE);

        if (empty($signature)) {
            return abort(400, 'Bad Request');
        }


        $events = $bot->parseEventRequest($request->getContent(), $signature);
//        Log::debug(__LINE__ . ' BOT:' . print_r((array)$bot, true));
//        Log::debug(__LINE__ . ' EVENT:' . print_r((array)$events, true));
        collect($events)->each(function ($event) use ($bot) {

            // メッセージタイプごとに処理を分ける　TEXT
            if ($event instanceof TextMessage) {
                $u = $this->__setUserProfile($bot, $event->getUserId());
                //
                $text = $this->__textMessageResponse($event, $u);
                if (is_object($text)) {
                    return $bot->replyMessage($event->getReplyToken(), $text);
                }
                return $bot->replyText($event->getReplyToken(), $text);
            }

            if ($event instanceof PostbackEvent) {
                $txt = "";
                $u = $this->__setUserProfile($bot, $event->getUserId());
                $postdata = $event->getPostbackData();
                Log::debug(__LINE__ . ' POSTDATA:' . $postdata);
                if ($postdata == "button=1") {
                    $yes_button = new PostbackTemplateActionBuilder('ON', 'button=11');
                    $no_button = new PostbackTemplateActionBuilder('OFF', 'button=10');
                    $actions = [$yes_button, $no_button];
                    $button = new ConfirmTemplateBuilder('朝の通知はどうしますか？', $actions);
                    $button_message = new TemplateMessageBuilder('通知設定 朝', $button);
                    return $bot->replyMessage($event->getReplyToken(), $button_message);
                } elseif ($postdata == "button=0") {
                    return $bot->replyText($event->getReplyToken(), "設定は変更しません");
                } elseif ($postdata == "button=11") {
                    $flg = $u->notification_flg;
                    // 11
                    $u->notification_flg = $this->__flgSettingType11($flg);
                    $u->save();
                    //
                    $yes_button = new PostbackTemplateActionBuilder('ON', 'button=21');
                    $no_button = new PostbackTemplateActionBuilder('OFF', 'button=20');
                    $actions = [$yes_button, $no_button];
                    $button = new ConfirmTemplateBuilder('昼の通知はどうしますか？', $actions);
                    $button_message = new TemplateMessageBuilder('通知設定 昼', $button);
                    return $bot->replyMessage($event->getReplyToken(), $button_message);
                } elseif ($postdata == "button=10") {
                    $flg = $u->notification_flg;
                    // 10
                    $u->notification_flg = $this->__flgSettingType10($flg);;
                    $u->save();
                    //
                    $yes_button = new PostbackTemplateActionBuilder('ON', 'button=21');
                    $no_button = new PostbackTemplateActionBuilder('OFF', 'button=20');
                    $actions = [$yes_button, $no_button];
                    $button = new ConfirmTemplateBuilder('昼の通知はどうしますか？', $actions);
                    $button_message = new TemplateMessageBuilder('通知設定 昼', $button);
                    return $bot->replyMessage($event->getReplyToken(), $button_message);
                } elseif ($postdata == "button=21") {
                    $flg = $u->notification_flg;
                    // 21
                    $u->notification_flg = $this->__flgSettingType21($flg);
                    $u->save();
                    //
                    $yes_button = new PostbackTemplateActionBuilder('ON', 'button=31');
                    $no_button = new PostbackTemplateActionBuilder('OFF', 'button=30');
                    $actions = [$yes_button, $no_button];
                    $button = new ConfirmTemplateBuilder('夜の通知はどうしますか？', $actions);
                    $button_message = new TemplateMessageBuilder('通知設定 夜', $button);
                    return $bot->replyMessage($event->getReplyToken(), $button_message);

                } elseif ($postdata == "button=20") {
                    $flg = $u->notification_flg;
                    // 20
                    $u->notification_flg = $this->__flgSettingType20($flg);
                    $u->save();
                    //
                    $yes_button = new PostbackTemplateActionBuilder('ON', 'button=31');
                    $no_button = new PostbackTemplateActionBuilder('OFF', 'button=30');
                    $actions = [$yes_button, $no_button];
                    $button = new ConfirmTemplateBuilder('夜の通知はどうしますか？', $actions);
                    $button_message = new TemplateMessageBuilder('通知設定 夜', $button);
                    return $bot->replyMessage($event->getReplyToken(), $button_message);

                } elseif ($postdata == "button=31") {
                    $flg = $u->notification_flg;
                    // 31
                    $u->notification_flg = $this->__flgSettingType31($flg);
                    $u->save();
                } elseif ($postdata == "button=30") {
                    $flg = $u->notification_flg;
                    // 30
                    $u->notification_flg = $this->__flgSettingType30($flg);
                    $u->save();
                } elseif ($postdata == "lang=false") {
                    return $bot->replyText($event->getReplyToken(), "設定は維持されました");
                } elseif ($postdata == "lang=true") {
//           0     "日本語",
                    if ($u->language != 0) {
                        $ja_JP = new PostbackTemplateActionBuilder('Japanese', 'lang=0');
                        $actions[] = $ja_JP;
                    }
//           1     "English",
                    if ($u->language != 1) {
                        $en_US = new PostbackTemplateActionBuilder('English', 'lang=1');
                        $actions[] = $en_US;
                    }
//           2     "Chinese",
                    if ($u->language != 2) {
                        $zh_CN = new PostbackTemplateActionBuilder('Chinese', 'lang=2');
                        $actions[] = $zh_CN;
                    }
//           3     "Español",
//           4     "Français",
//           5     "Português",

//           6     "Indonesia",
//                    if ($u->language != 6) {
//                        $id_ID = new PostbackTemplateActionBuilder('Indonesia', 'lang=6');
//                        $actions[] = $id_ID;
//                    }
//           7     "Deutsch",

//           8     "Thai",
                    if ($u->language != 8) {
                        $th_TH = new PostbackTemplateActionBuilder('Thai', 'lang=8');
                        $actions[] = $th_TH;
                    }
//           9     "Korean",
                    if ($u->language != 9) {
                        $ko_KR = new PostbackTemplateActionBuilder('Korean', 'lang=9');
                        $actions[] = $ko_KR;
                    }
//           10    "Taiwan",
//                    if ($u->language != 10) {
//                        $zh_TW = new PostbackTemplateActionBuilder('Taiwan', 'lang=10');
//                        $actions[] = $zh_TW;
//                    }
//                  $actions = [$ja_JP, $en_US, $th_TH, $zh_TW, $id_ID];
//                  $actions = [$ja_JP, $en_US, $th_TH, $id_ID];
                    $button = new ButtonTemplateBuilder("言語設定", 'どの言語に設定しますか？', null, $actions);
                    $button_message = new TemplateMessageBuilder('言語設定', $button);
                    return $bot->replyMessage($event->getReplyToken(), $button_message);

                } elseif ($postdata == "lang=0") {
//           0     "日本語",
                    $txt = "設定言語を日本語に設定しました";
                    $u->language = 0;
                    $u->save();
                } elseif ($postdata == "lang=1") {
//           1     "English",
                    $txt = "Language set to English.";
                    $u->language = 1;
                    $u->save();
                } elseif ($postdata == "lang=2") {
//           2     "Chinese",
                    $txt = "设置语言为中文";
                    $u->language = 2;
                    $u->save();
                } elseif ($postdata == "lang=3") {
//           3     "Español",
                    $txt = "Establecer idioma en español";
                    $u->language = 3;
                    $u->save();
                } elseif ($postdata == "lang=4") {
//           4     "Français",
                    $txt = "Définir la langue sur le français";
                    $u->language = 4;
                    $u->save();
                } elseif ($postdata == "lang=5") {
//           5     "Português",
                    $txt = "Definir idioma para português";
                    $u->language = 5;
                    $u->save();
                } elseif ($postdata == "lang=6") {
//           6     "Indonesia",
                    $txt = "Bahasa disetel ke bahasa Indonesia";
                    $u->language = 6;
                    $u->save();
                } elseif ($postdata == "lang=7") {
//           7     "Deutsch",
                    $txt = "Sprache auf Deutsch einstellen";
                    $u->language = 7;
                    $u->save();
                } elseif ($postdata == "lang=8") {
//           8     "Thai",
                    $txt = "ภาษาที่ตั้งไว้เป็นภาษาไทย";
                    $u->language = 8;
                    $u->save();
                } elseif ($postdata == "lang=9") {
//           9     "Korean",
                    $txt = "설정 언어를 한국어로 설정했습니다.";
                    $u->language = 9;
                    $u->save();
                } elseif ($postdata == "lang=10") {
//           10    "Taiwan",
                    $txt = "語言設定為台灣中文";
                    $u->language = 10;
                    $u->save();
                }
                if ($txt != "") {
                    return $bot->replyText($event->getReplyToken(), $txt);
                }
                return $bot->replyText($event->getReplyToken(), "設定しました");
            }

            // メッセージタイプごとに処理を分ける　友だち登録
            if ($event instanceof FollowEvent) {
                sleep(2); // 自動返答があるので2秒末といいらしい
                $u = $this->__setUserProfile($bot, $event->getUserId());
                return $bot->replyText($event->getReplyToken(), '友達登録されたよ！');
            }
            // メッセージタイプごとに処理を分ける　ファイルアップロード 非画像
            if ($event instanceof FileMessage) {
                $u = $this->__setUserProfile($bot, $event->getUserId());
                $this->__LineFileUpload($event, $bot);
                return $bot->replyText($event->getReplyToken(), 'ファイルのアップロードが完了しました！');
            }

            if ($event instanceof AudioMessage) {
                $u = $this->__setUserProfile($bot, $event->getUserId());
                $this->__LineFileUpload($event, $bot);
                return $bot->replyText($event->getReplyToken(), '発信');
            }

            // メッセージタイプごとに処理を分ける　イメージアップロード 画像
            if ($event instanceof ImageMessage) {
                $u = $this->__setUserProfile($bot, $event->getUserId());

                $messageId = $event->getMessageId();
                $response = $bot->getMessageContent($messageId);
                $fileUrl = $this->uploadImageToCloudAppThenGetUrl($response->getRawBody());
////              appendMessage($event->getUserId(), $event->getGroupId(), $event->getTimestamp(), $event->getMessageId(), $event->getMessageType(), $fileUrl);

                $base_name = basename($fileUrl);
                $urlpath = "https://daocar.llc/base/" . $base_name;
//              $response = $this->face_checker($fileUrl);

                try {
                    //トランザクション開始
                    DB::beginTransaction();

                    if (isset($u->id)) {
                        $user_id = $u->id;
                    } else {
                        $user_id = 0;
                    }

                    $records = ImageData::create(
                        [
                            'ulid' => (string)Str::ulid(),
                            'user_id' => $user_id,
                            'line_mid' => $event->getUserId(),
                            'file_name' => $fileUrl,
                            'file_md5' => md5_file($fileUrl),
                            'img_type' => 1,
                            'model_url' => $urlpath
                        ]
                    );
                    DB::commit();
                    //トランザクション終了
                    Log::debug('画像登録完了：' . print_r($records, true));
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::debug('画像登録失敗：' . print_r($e, true));
                }
                $responseMessage = '写真のアップロードが完了しました！解析結果をお待ち下さい。';
                return $bot->replyText($event->getReplyToken(), $responseMessage);
            }

            // メッセージタイプごとに処理を分ける　位置情報
            if ($event instanceof LocationMessage) {
                $message = $this->__getGpsNear($bot, $event, $event->getLatitude(), $event->getLongitude());
                $yes_button = new PostbackTemplateActionBuilder('依頼する', 'GO');
                $no_button = new PostbackTemplateActionBuilder('依頼しない', 'NONE');
                $actions = [$yes_button, $no_button];
                $button = new ConfirmTemplateBuilder($message.'配車依頼しますか？', $actions);
                $button_message = new TemplateMessageBuilder('配車依頼', $button);
                return $bot->replyMessage($event->getReplyToken(), $button_message);
//                return $bot->replyText($event->getReplyToken(), $message);
            }

            // メッセージタイプごとに処理を分ける　スタンプ
            if ($event instanceof StickerMessage) {
                $bot->replyText($event->getReplyToken(), 'スタンプわからん');

            } else {
                $bot->replyText($event->getReplyToken(), '未対応のメッセージ形式:' . print_r($event, true));
            }

        });

        return;
    }

    /**
     * 近くの車を返す（現状走ってないのでダミー）
     * @param $bot
     * @param $event
     * @param $lat
     * @param $lon
     * @return string
     */
    private function __getGpsNear($bot, $event, $lat = "", $lon = "")
    {
        $message = "";
        $message .= "15分程度で到着します。\n";
        $message .= "車種は黒のマイバッハです。\n";
//        $message .= "到着しましたらお知らせします。\n";
        return $message;
    }


    /**
     * @param $flg
     * @return float|int|mixed
     */
    private function __flgSettingType11($flg)
    {
        // 11
        switch ($flg) {
            case bindec("100"):
            case bindec("000"):
                $flg = bindec("100");
                break;
            case bindec("101"):
            case bindec("001"):
                $flg = bindec("101");
                break;
            case bindec("110"):
            case bindec("010"):
                $flg = bindec("110");
                break;
            case bindec("111"):
            case bindec("011"):
                $flg = bindec("111");
                break;
        }
        return $flg;
    }

    /**
     * @param $flg
     * @return float|int|mixed
     */
    private function __flgSettingType10($flg)
    {
        // 10
        switch ($flg) {
            case bindec("100"):
            case bindec("000"):
                $flg = bindec("000");
                break;
            case bindec("101"):
            case bindec("001"):
                $flg = bindec("001");
                break;
            case bindec("110"):
            case bindec("010"):
                $flg = bindec("010");
                break;
            case bindec("111"):
            case bindec("011"):
                $flg = bindec("011");
                break;
        }
        return $flg;
    }

    /**
     * @param $flg
     * @return float|int|mixed
     */
    private function __flgSettingType21($flg)
    {
        // 21
        switch ($flg) {
            case bindec("000"):
            case bindec("010"):
                $flg = bindec("010");
                break;
            case bindec("001"):
            case bindec("011"):
                $flg = bindec("011");
                break;
            case bindec("100"):
            case bindec("110"):
                $flg = bindec("110");
                break;
            case bindec("111"):
            case bindec("101"):
                $flg = bindec("111");
                break;
        }
        return $flg;
    }

    /**
     * @param $flg
     * @return float|int|mixed
     */
    private function __flgSettingType20($flg)
    {
        // 20
        switch ($flg) {
            case bindec("010"):
            case bindec("000"):
                $flg = bindec("000");
                break;
            case bindec("011"):
            case bindec("001"):
                $flg = bindec("001");
                break;
            case bindec("110"):
            case bindec("100"):
                $flg = bindec("100");
                break;
            case bindec("111"):
            case bindec("101"):
                $flg = bindec("101");
                break;
        }
        return $flg;
    }

    /**
     * @param $flg
     * @return float|int|mixed
     */
    private function __flgSettingType31($flg)
    {
        // 31
        switch ($flg) {
            case bindec("000"):
            case bindec("001"):
                $flg = bindec("001");
                break;
            case bindec("010"):
            case bindec("011"):
                $flg = bindec("011");
                break;
            case bindec("100"):
            case bindec("101"):
                $flg = bindec("101");
                break;
            case bindec("110"):
            case bindec("111"):
                $flg = bindec("111");
                break;
        }
        return $flg;
    }

    /**
     * @param $flg
     * @return float|int|mixed
     */
    private function __flgSettingType30($flg)
    {
        // 30
        switch ($flg) {
            case bindec("000"):
            case bindec("001"):
                $flg = bindec("000");
                break;
            case bindec("010"):
            case bindec("011"):
                $flg = bindec("010");
                break;
            case bindec("100"):
            case bindec("101"):
                $flg = bindec("100");
                break;
            case bindec("110"):
            case bindec("111"):
                $flg = bindec("110");
                break;
        }
        return $flg;
    }

    /**
     * @param $bot
     * @param $line_mid
     * @return null
     */
    private function __setUserProfile($bot, $line_mid = "")
    {
        if ($line_mid != "") {

            $response = $bot->getProfile($line_mid);
            $profile = $response->getJSONDecodedBody();
            Log::debug(__LINE__ . ' USER Profile:' . print_r((array)$profile, true));

            if (isset($profile["displayName"])) {
                $userName = $profile["displayName"];
            } else {
                $userName = "";
            }
            Log::debug(__LINE__ . ' USER userName:' . $userName);

            if (isset($profile["pictureUrl"])) {
                $pictureUrl = $profile["pictureUrl"];
            } else {
                $pictureUrl = "";
            }
            Log::debug(__LINE__ . ' USER pictureUrl:' . $pictureUrl);

            if (isset($profile["language"])) {
                $line_language = $profile["language"];
                switch ($line_language) {
                    case "ja":
                        $language = 0;// ja_JP
                        break;
                    case "th":
                        $language = 8;// th_TH
                        break;
                    case "tw":
                        $language = 10;// zh_TW
                        break;
                    case "id":
                        $language = 6;// id_ID
                        break;
                    default:
//           0     "日本語",
//           1     "English",
//           2     "Chinese",
//           3     "Español",
//           4     "Français",
//           5     "Português",
//           6     "Indonesia",
//           7     "Deutsch",
//           8     "Thai",
//           9     "Korean",
//           10    "Taiwan",
                        $language = 1; // en_US
                }
            } else {
                $language = 0;
            }
            Log::debug(__LINE__ . ' USER language:' . sprintf("%d", $language));

            $u = User::Select()->where('line_mid', $line_mid)->first();
//          Log::debug(__LINE__ . ' USER:' . print_r((array)$l, true));
            if (!$u) {
                $user = new User();
                $user->create([
                    'name' => $userName,
                    'line_mid' => $line_mid,
                    'picture_url' => $pictureUrl,
                    'language' => $language,
                ]);
                sleep(1);
                $u = User::Select()->where('line_mid', $line_mid)->first();
            } else {
                if ($u->name == "") {
                    $u->name = $userName;
                }
                if ($u->picture_url == "") {
                    $u->picture_url = $pictureUrl;
                }
                $u->save();
            }
            return $u;

        }
        return null;

    }


    /**
     *
     * @param $event
     * @param $u
     * @return TextMessageBuilder|string
     */
    private function __textMessageResponse($event, $u = null)
    {

        $text = "";
        $command = $event->getText();
        if (strtoupper(trim($command)) == strtoupper("systemcommand")) {
            $text .= "設定" . "\n";
            $text .= "" . "\n";
//          $text .= "Channel チャンネル設定" . "\n";
            $text .= "Language 言語設定" . "\n";
            $text .= "Notification 通知";
        }

        if (strtoupper(trim($command)) == strtoupper("NEWS")) {
            $text .= "NEWS" . "\n";
            $text .= "2024/06/13 設定がLINEからできるようになりました。" . "\n";
            $text .= "2024/06/13 通知バッチがバグってたのを直しました。ごめんなさい。" . "\n";
            $text .= "2024/06/13 設定確認機能をわかりやすく改修しました。";
        }


        if (strtoupper(trim($command)) == strtoupper("Setting")) {

            // クイックリプライボタン
            $quick_reply_buttons = [
//              "チャンネル" => "Channel",
                "言語" => "Language",
                "通知" => "Notification",
            ];
            $buttons = [];
            foreach ($quick_reply_buttons as $button => $quick_reply_button) {
                // 1、表示する文言と押下時に送信するメッセージをセット
                $button_template_action_builder = new MessageTemplateActionBuilder($button, $quick_reply_button);
                $quick_reply_button_builder = new QuickReplyButtonBuilder($button_template_action_builder);
                // 3、ボタンを配列に格納する(12個まで)
                $buttons[] = $quick_reply_button_builder;
            }
//          Log::debug(__LINE__ . ' BUTTONS:' . print_r($buttons,true));

            $quick_reply_message_builder = new QuickReplyMessageBuilder($buttons);
            $text = new TextMessageBuilder('確認したい設定を選択してください', $quick_reply_message_builder);
//            Log::debug(__LINE__ . ' BUTTONS:' . print_r($text, true));
//            $bot->replyMessage($reply_token, $text);
            return $text;
        }

        if (strtoupper(trim($command)) == strtoupper("language")) {
            $langA = array(
                "日本語",
                "English",
                "Chinese",
                "Español",
                "Français",
                "Português",
                "Indonesia",
                "Deutsch",
                "Thai",
                "Korean",
                "Taiwan",
            );

            $text .= "Language 言語設定" . "\n\n";
            $text .= "現在の設定：" . "";
            if (isset($u->language) && isset($langA[$u->language])) {
                $text .= $langA[$u->language];
            }
            $text .= "" . "\n";
            $text .= "" . "\n";
            $text .= "※現状、AIからのレスポンスのみ翻訳されます" . "\n";
            $text .= "" . "\n";
//            // ここで言語設定を変える
//            $text .= "Japanese" . "\n";// ja_JP　0
//            $text .= "English" . "\n";// en_US 1
//            $text .= "Thai" . "\n";// th_TH 2
//            $text .= "Taiwan" . "\n";// zh_TW 3
//            $text .= "Indonesia" . "\n";// id_ID 4

            $yes_button = new PostbackTemplateActionBuilder('はい', 'lang=true');
            $no_button = new PostbackTemplateActionBuilder('いいえ', 'lang=false');
            $actions = [$yes_button, $no_button];
            $button = new ConfirmTemplateBuilder($text . '設定を変更しますか？', $actions);
            $button_message = new TemplateMessageBuilder('言語設定', $button);

            return $button_message;
        }

//        if (strtoupper(trim($command)) == strtoupper("Channel")) {
//            $text .= "Chを4桁の数字で入力ください(0000はNG)" . "\n";
//            $text .= "現在のCh：";
//            if (isset($u->channel) && (int)sprintf("%d", $u->channel) > 0 && (int)sprintf("%d", $u->channel) < 10000) {
//                $text .= sprintf("%04d Ch", $u->channel);
//            } else {
//                $text .= "Ch未セット";
//            }
//            //
//        }
//
//        if (strlen(trim($command)) == 4 && preg_match("/^[0-9]+$/", trim($command))) {
//            $text .= "Chを変更します" . "\n";
//            $text .= "現在のCh：";
//            $new_num = mb_convert_kana(trim($command), "n");
//            if (isset($u->channel) && $u->channel > 0) {
//                $text .= sprintf("%d Ch". "\n", $u->channel);
//            } else {
//                $text .= "Ch未セット". "\n";
//            }
//            $text .= "変更後のCh：";
//            $text .= sprintf("%04d Ch", $new_num);
//            //
//            $u->channel = $new_num;
//            $u->save();
//        }

        if (strtoupper(trim($command)) == strtoupper("notification")) {
            $text .= "通知設定" . "\n";
            $text .= "現在の通知設定：" . "";
            if (isset($u->notification_flg) && $u->notification_flg >= 0) {
                $set = [];
                // 朝　昼　夜
                $set[bindec("000")] = "すべてOFF";
                $set[bindec("001")] = "夜のみON";
                $set[bindec("010")] = "昼のみON";
                $set[bindec("011")] = "昼と夜ON";
                $set[bindec("100")] = "朝のみON";
                $set[bindec("101")] = "昼と夜ON";
                $set[bindec("110")] = "朝と昼ON";
                $set[bindec("111")] = "朝昼夜ON";
                Log::debug(__LINE__ . ' Notification:' . decbin($u->notification_flg));
                $text .= sprintf("%s\n", $set[$u->notification_flg]);
            }
            //
            $yes_button = new PostbackTemplateActionBuilder('はい', 'button=1');
            $no_button = new PostbackTemplateActionBuilder('いいえ', 'button=0');
            $actions = [$yes_button, $no_button];
            $button = new ConfirmTemplateBuilder($text . '設定を変更しますか？', $actions);
            $button_message = new TemplateMessageBuilder('通知設定', $button);
            return $button_message;
        }

        if ($text == "") {
            $text = $command;
//          $text = $this-> __ai_res($command);
        }
        return $text;

    }

    /**
     * @param $lineId
     * @param $text
     * @return LINEBot\Response
     */
    private function __LineSendMessagel($lineId = "", $text = "")
    {
        $httpClient = new CurlHTTPClient(config('services.line.message.channel_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('services.line.message.channel_secret')]);

        Log::debug(__LINE__ . ' SEND MESSAGE:' . $text);
        // メッセージを作成する
        $message = new TextMessageBuilder($text);
        // メッセージを送信する
        return $bot->pushMessage($lineId, $message);
    }

    private function __LineFileUpload($event, $bot)
    {
        $messageId = $event->getMessageId();
        $response = $bot->getMessageContent($messageId);
        $fileUrl = $this->uploadTxtToCloudAppThenGetUrl($response->getRawBody());

        try {
            //トランザクション開始
            DB::beginTransaction();

            $records = FileData::create(
                [
                    'ulid' => (string)Str::ulid(),
                    'user_id' => 1,
                    'line_mid' => $event->getUserId(),
                    'file_name' => $fileUrl,
                    'file_md5' => md5_file($fileUrl),
                    'file_type' => 1,
                ]
            );
            DB::commit();
            //トランザクション終了
            Log::debug('ファイル登録完了：' . print_r($records, true));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug('ファイル登録失敗：' . print_r($e, true));
        }

        Log::debug('ファイル登録完了：' . print_r($fileUrl, true));
        return true;
    }

    private function __ai_res($prompt = "")
    {
        $message = array(
            "role" => "user",
            "content" => "Hello, world",
        );

        $data = array(
            'model' => 'claude-3-opus-20240229',
            "max_tokens" => 100,
            "messages" => $message,
//          'prompt' => "\n\nHuman: " . $prompt . "\n\nAssistant:", // Be sure to format prompt appropriately
//          'stop_sequences' => array("\n\nHuman:")
        );
        $response = $this->__anthropicMessage($data);
//      Log::debug(__LINE__ . ' DEBUG:', (array)$response);
        return "";
    }

    /**
     * @param $data
     * @return mixed
     */
    private function __anthropicMessage($data)
    {

        $apiKey = config('services.anthropic.apikey');
        $anthropicVersion = "2023-06-01";

        $options = array(
            CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'x-api-key: ' . $apiKey,
                'anthropic-version: ' . $anthropicVersion,
                'content-type: application/json'
            ),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data)
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $api_response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($api_response, true);

        return $response;
    }

    /**
     * AI画像変換
     * @param $command
     * @param $fileName
     * @param $mask
     * @return false|mixed
     */
    public function generateResponse($command = "", $fileName = "", $mask = "")
    {
        $directory_path = "";

        // ファイルがないときはFALSE
        if (!file_exists($directory_path . $fileName)) {
            return false;
        }

        $result = OpenAI::images()->edit([
            'image' => file_get_contents($directory_path . $fileName),
//          'mask' => file_get_contents($mask),
            'prompt' => $command,
            'n' => 4,
            // 'size' => '256x256',
            'size' => '512x512',
            'response_format' => 'url'
        ]);

        return $result['data'];
        //     return $result['data'][0]['url'];
    }


    /**
     * @param Request $request
     * @return void
     */
    public function chat(Request $request)
    {
        // ファイルはPNG
        $fileName = "sample.png";//
        // ファイルは正方形
        // 人物は1人限定

        $command = 'change another matching .';
        if ($command != null) {
            $response = $this->generateResponse($command, $fileName);

//          Log::debug(__LINE__ . ' image:', (array)$response);
            print_r($response);

        }
        return;
    }


    public function imagein(Request $request)
    {
        $inputText = $request->food;

        if ($inputText != null) {

            // generateResponseメソッドに処理を受け渡す
            $responseText = $this->generateResponse($inputText);

            $messages = [
                ['title' => 'Tell me what  you', 'content' => $inputText],
                ['title' => 'から', 'content' => $responseText]
            ];

            // generateImageメソッドに処理を受け渡す
            $image = $this->generateImage($responseText);
            print_r($image);
            return;
        }
        return;
    }

    /**
     * @param $image_path
     * @return string
     */
    public function removeBackground($image_path = "")
    {

        $image_base64 = base64_encode(file_get_contents($image_path));

        $response = OpenAI::completions()->create([
            'model' => 'removebg',
            'image' => $image_base64,
        ]);

        if (isset($response['data']['base64'])) {
            $output_image = base64_decode($response['data']['base64']);
            $output_image_path = public_path("base/" . 'ds3.png');
            file_put_contents($output_image_path, $output_image);
            return '背景を除去した画像を保存しました。';
        } else {
            return 'エラーが発生しました。';
        }
    }

    /**
     * @param $srcFile
     * @param $dstFile
     * @return true
     */
    public function removebg($srcFile = "", $dstFile = "")
    {

        $apiKey = "";
        $removebg = new RemoveBg($apiKey);
        $removebg->file($srcFile)->save($dstFile);

        return true;
    }

    public function generateResponse2($inputText = "")
    {

        $result = OpenAI::completions()->create([
            'model' => 'DALL·E 2',
            'prompt' => $inputText,
            'n' => 4,
            'size' => '512x512',
            'response_format' => 'url',

        ]);

        return $result['choices'][0]['text'];

    }


    /**
     * 画像を作成 openai API
     * @param $responseText
     * @return mixed
     */
    public function generateImage($responseText)
    {
        $response = OpenAI::images()->create([
            'prompt' => $responseText,
            'n' => 1,
            'size' => '256x256',
            'response_format' => 'url',
        ]);
        return $response['data'][0]['url'];
    }

    /**
     * @param $userId
     * @param $groupId
     * @param $timestamp
     * @param $messageid
     * @param $messageType
     * @param $messageBody
     * @return void
     */
    function appendMessage($userId, $groupId, $timestamp, $messageid, $messageType, $messageBody)
    {


    }


    /**
     * @param $rawBody
     * @return string
     */
    public function uploadTxtToCloudAppThenGetUrl($rawBody)
    {

//        $im = imagecreatefromstring($rawBody);
//        Log::debug(__LINE__ . ' im:', (array)$rawBody);

        $resultString = "";
//        if ($im !== false) {
        $filename = uniqid();
        $directory_path = "/var/www/yaseme/laravel/storage/app/public/base";
        if (!file_exists($directory_path)) {
            if (mkdir($directory_path, 0777, true)) {
                chmod($directory_path, 0777);
            }
        }
        file_put_contents($directory_path . "/" . $filename . ".txt", $rawBody);
//        } else {
//            error_log("fail to create txt.");
//        }

        $path = $directory_path . "/" . $filename . ".txt";
//        Log::debug(__LINE__ . ' path:', (array)$path);

        return $path;
    }

    /**
     * 画像のアップロード
     * @param $rawBody
     * @return string
     */
    public function uploadImageToCloudAppThenGetUrl($rawBody)
    {

        $im = imagecreatefromstring($rawBody);
//        Log::debug(__LINE__ . ' im:', (array)$im);

        $resultString = "";
        if ($im !== false) {
            $filename = uniqid();
            $directory_path = "/var/www/yaseme/laravel/storage/app/public/base";
            if (!file_exists($directory_path)) {
                if (mkdir($directory_path, 0777, true)) {
                    chmod($directory_path, 0777);
                }
            }
            imagejpeg($im, $directory_path . "/" . $filename . ".jpg", 75);
        } else {
            error_log("fail to create image.");
        }

        $path = $directory_path . "/" . $filename . ".jpg";
//        Log::debug(__LINE__ . ' path:', (array)$path);

        return $path;
    }

    /**
     * @param Request $request
     * @return void
     */
    public function webhook(Request $request)
    {
        $lineAccessToken = env('LINE_ACCESS_TOKEN', "");
        $lineChannelSecret = env('LINE_CHANNEL_SECRET', "");

        // 署名のチェック
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
        if (!SignatureValidator::validateSignature($request->getContent(), $lineChannelSecret, $signature)) {
            // TODO 不正アクセス
            return;
        }

        $httpClient = new CurlHTTPClient ($lineAccessToken);
        $lineBot = new LINEBot($httpClient, ['channelSecret' => $lineChannelSecret]);

        try {
            // イベント取得
            $events = $lineBot->parseEventRequest($request->getContent(), $signature);

            foreach ($events as $event) {
                // ハローと応答する
                $replyToken = $event->getReplyToken();
                $textMessage = new TextMessageBuilder("ハロー");
                $lineBot->replyMessage($replyToken, $textMessage);
            }
        } catch (Exception $e) {
            // TODO 例外
            return;
        }
        return;
    }
}
