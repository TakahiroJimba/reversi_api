<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Http\Controllers\Controller;    // ディレクトリ階層が別な場合に必要
use App\Mail\RegisterShipped;
use Illuminate\Support\Facades\App;
use Mail;

// 「\」を入れないで使うには下記の一文を入れておくこと
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // ユーザ仮登録
    public function regist()
    {
        $ret_data["is_success"] = false;

        // パラメータ取得
        $mail_address = $_POST["mail_address"];
        $name = $_POST["name"];
        $password = $_POST["password"];

        // バリデーション
        // TODO: 実装

        // 仮登録処理
        $now = Carbon::now();
        // 4桁の認証コードを生成
        $pass_phrase = "";
        for ($i = 0; $i < USER_REGISTRATION_PASS_PHRASE_LENGTH; $i++) {
            $pass_phrase .= rand(0, 9);
        }

        $temp_user = [
            'mail_address'  => $mail_address,
            'name'          => $name,
            'password'      => Hash::make($password),
            'pass_phrase'   => $pass_phrase,
            'created_at'    => $now,
            'updated_at'    => $now,
        ];

        try
        {
            DB::table("temp_users")->insert($temp_user);
        }
        catch (\Exception $e)
        {
            $err_msg = "登録処理に失敗しました。<br>管理者へお問い合わせください。";
            $ret_data["err_msgs"] = array($err_msg);
            log::error($err_msg);
            log::error($e->getMessage());
            return json_encode($ret_data);
        }

        // 仮登録完了メールを送信する
        $sendData = [
            'pass_phrase' => $pass_phrase,
        ];
        Mail::to($mail_address)->send(new RegisterShipped($sendData));

        $ret_data["is_success"] = true;
        return json_encode($ret_data);
    }
}
