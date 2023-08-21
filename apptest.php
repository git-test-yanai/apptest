<?php

// パラメータ類
$authorization_endpoint = 'https://login.microsoftonline.com/SSOtest20210301.onmicrosoft.com/oauth2/v2.0/authorize?p=b2c_1_signintest';
$token_endpoint = 'https://login.microsoftonline.com/SSOtest20210301.onmicrosoft.com/oauth2/v2.0/token?p=b2c_1_signintest';
$client_id = '6e4d6776-fa10-42a7-869d-b6978b9b3251';
$client_secret = 'PPf8Q~PGK5JLfP5ejtbVh0mvhh1hUc2OXws8Hdbw';
$redirect_uri = 'http://localhost/apptest.php';
// $redirect_uri = 'https://xxxx.azurewebsites.net/index.php';
$response_type = 'code';
$state =  'hogehoge'; // 手抜き
$nonce = 'fogafoga'; // 手抜き

// codeの取得(codeがパラメータについてなければ初回アクセスとしてみなしています。手抜きです)
$req_code = $_GET['code'];
if(!$req_code){
    // 初回アクセスなのでログインプロセス開始
    // GETパラメータ関係
    $query = http_build_query(array(
        'client_id'=>$client_id,
        'response_type'=>$response_type,
        'redirect_uri'=> $redirect_uri,
        'scope'=>'openid',
        'state'=>$state,
        'nonce'=>$nonce
    ));
    // リクエスト
    header('Location: ' . $authorization_endpoint . '&' . $query );
    exit();
}

// POSTデータの作成
$postdata = array(
    'grant_type'=>'authorization_code',
    'client_id'=>$client_id,
    'code'=>$req_code,
    'client_secret'=>$client_secret,
    'redirect_uri'=>$redirect_uri
);

// TokenエンドポイントへPOST
$ch = curl_init($token_endpoint);
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$response = json_decode(curl_exec($ch));
curl_close($ch);

// id_tokenの取り出しとdecode
$id_token = explode('.', $response->id_token);
$payload = base64_decode(str_pad(strtr($id_token[1], '-_', '+/'), strlen($id_token[1]) % 4, '=', STR_PAD_RIGHT));
$payload_json = json_decode($payload, true);

// 整形と表示
print<<<EOF
    <html>
    <head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <title>Obtained claims</title>
    </head>
    <body>
    <table border=1>
    <tr><th>Claim</th><th>Value</th></tr>
EOF;
    foreach($payload_json as $key => $value){
        if($key == "emails"){
            foreach($value as $mail_key => $mail_value){
                print('<tr><td>'.$key.'</td><td>'.$mail_value.'</td></tr>');                    
            }
        }else{
            print('<tr><td>'.$key.'</td><td>'.$value.'</td></tr>');            
        }
    }
print<<<EOF
    </table>
    </body>
    </html>
EOF;

?>