<?php
$access_token = 'wSglDfYhfE/lDsi44V0pB19s+ib2bUeE5rrjeFWwTsQnyEFHLmXHOaTPXicqwpowxgrkbMxfnFsWy74GOjc+uRF21EJTEvnwT9by1lOo7RWmzQGNF4Rh59sqK5flz2Y1lqUgFakghR8tX8K26uXWkFGUYhWQfeY8sLGRXgo3xvw=';

// APIから送信されてきたイベントオブジェクトを取得
$json_string = file_get_contents('php://input');

// 受け取ったJSON文字列をデコード
$json_obj = json_decode($json_string);

// このイベントへの応答に使用するトークン
$reply_token = $json_obj->{'events'}[0]->{'replyToken'};

// イベント種別（今回は2種類のみ）
// message（メッセージが送信されると発生）
// postback（ポストバックオプションに返事されると送信）
$type = $json_obj->{'events'}[0]->{'type'};

// メッセージオブジェクト（今回は4種類のみ）
// text（テキストを受け取った時）
// sticker（スタンプを受け取った時）
// image（画像を受け取った時）
// location（位置情報を受け取った時）
$msg_obj = $json_obj->{'events'}[0]->{'message'}->{'type'};

if($type === 'message') {
    // メッセージ受け取り時
    if($msg_obj === 'text') {
        // テキストを受け取った時
        $msg_text = $json_obj->{'events'}[0]->{'message'}->{'text'};
        if($msg_text === '予約') {
            $message = array(
                'type' => 'template',
                'altText' => 'いつのご予約ですか？',
                'template' => array(
                    'type' => 'confirm',
                    'text' => 'いつのご予約ですか？',
                    'actions' => array(
                        array(
                            'type' => 'postback',
                            'label' => '予約しない',
                            'data' => 'action=back'
                        ), array(
                            'type' => 'datetimepicker',
                            'label' => '期日を指定',
                            'data' => 'datetemp',
                            'mode' => 'date'// date：日付を選択します。time：時刻を選択します。datetime：日付と日時を選択します。
                        )
                    )
                )
            );
        } else {
            $message = array(
                'type' => 'text',
                'text' => '【'.$msg_text.'】とは何ですか？'
            );
        }
    } elseif($msg_obj === 'sticker') {
        // スタンプを受け取った時
        $message = array(
            'type' => 'sticker',
            'packageId' => '1',
            'stickerId' => '3'
        );
    } elseif($msg_obj === 'image') {
        // 画像を受け取った時
        $message = array(
            'type' => 'image',
            // オリジナル画像（タップしたら表示される画像）
            'originalContentUrl' => 'https://api.reh.tw/line/bot/example/assets/images/example.jpg',
            // サムネイル画像（トーク中に表示される画像）
            'previewImageUrl' => 'https://api.reh.tw/line/bot/example/assets/images/example.jpg'
        );
    } elseif($msg_obj === 'location') {
        // 位置情報を受け取った時
        $message = array(
            'type' => 'location',
            'title' => '皇居',
            'address' => '〒100-8111 東京都千代田区千代田１−１',
            'latitude' => 35.683798,
            'longitude' => 139.754182
        );
    }
} else if($type === 'postback') {
    // ポストバック受け取り時

    // 送られたデータ
    $postback = $json_obj->{'events'}[0]->{'postback'}->{'data'};

    if($postback === 'datetemp') {
        // 日にち選択時
        $message = array(
            'type' => 'text',
            'text' => '【'.$json_obj->{'events'}[0]->{'postback'}->{'params'}->{'date'}.'】にご予約を承りました。'
        );
    } elseif($postback === 'action=back') {
        // 戻る選択時
        $message = array(
            'type' => 'text',
            'text' => '何もしませんでした。'
        );
    }
}

$post_data = array(
    'replyToken' => $reply_token,
    'messages' => array($message)
);

// CURLでメッセージを返信する
$ch = curl_init('https://api.line.me/v2/bot/message/reply');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $access_token
));
$result = curl_exec($ch);
curl_close($ch);
