<?php
    // 【定数宣言】
    // システム全般
    define('APP_NAME',              'シンプルリバーシ');
    define('ADMIN_MAIL_ADDRESS',    'wpstore.pro.adm@gmail.com');

    // ユーザ
    define('USER_NAME_MAX_LENGTH',                  10);
    define('USER_PASSWORD_MIN_LENGTH',              8);
    define('USER_PASSWORD_MAX_LENGTH',              20);
    define('USER_REGISTRATION_PASS_PHRASE_LENGTH',  4);
    define('USER_REGISTRATION_AUTH_EXPIRATION',     1);

    // 別ドメインからのアクセスを受け付ける場合に必要
    header("Access-Control-Allow-Origin: *");
?>
