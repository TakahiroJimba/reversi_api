<?php
    // 【定数宣言】
    // システム全般
    define('APP_NAME',              'シンプルリバーシ');
    define('ADMIN_MAIL_ADDRESS',    'wpstore.pro.adm@gmail.com');

    // マスタ系
    define('RESULT_ID_WIN',                  1);
    define('RESULT_ID_LOSE',                 2);
    define('RESULT_ID_DRAW',                 3);
    define('RESULT_ID_SURRENDER',            51);
    define('RESULT_ID_OPPONENT_SURRENDER',   52);
    define('RESULT_ID_DISCONNECT',           91);
    define('RESULT_ID_OPPONENT_DISCONNECT',  92);

    define('GAME_MODE_ID_OFFLINE',           1);
    define('GAME_MODE_ID_AI',                11);
    define('GAME_MODE_ID_ONLINE_ROOM',       21);
    define('GAME_MODE_ID_ONLINE_FREE',       22);

    // // ユーザ
    // define('USER_NAME_MAX_LENGTH',                  10);
    // define('USER_PASSWORD_MIN_LENGTH',              8);
    // define('USER_PASSWORD_MAX_LENGTH',              20);
    // define('USER_REGISTRATION_PASS_PHRASE_LENGTH',  4);
    // define('USER_REGISTRATION_AUTH_EXPIRATION',     1);

    // 別ドメインからのアクセスを受け付ける場合に必要
    // header("Access-Control-Allow-Origin: *");
?>
