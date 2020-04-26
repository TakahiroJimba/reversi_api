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

        // --- バリデーション ---
        // アプリ側で実施したものと同様にバリデーション
        $err_msgs = $this->validate_user_basic_info($mail_address, $name, $password);
        if (isset($err_msgs) && count($err_msgs) != 0)
        {
            $ret_data["err_msgs"] = $err_msgs;
            log::error("バリデーションエラー 不正な操作がされた可能性があります。".$mail_address." ".$name." ".$password);
            return json_encode($ret_data);
        }

        // メールアドレス、ニックネームの重複チェック
        if(!$this->is_unique_mail_address($mail_address))
        {
            $err_msg = "すでに登録されているメールアドレスです。";
            $ret_data["err_msgs"] = array($err_msg);
            log::warning($mail_address."はすでに登録されています。");
            return json_encode($ret_data);
        }
        if(!$this->is_unique_name($name))
        {
            $err_msg = "すでに登録されているニックネームです。";
            $ret_data["err_msgs"] = array($err_msg);
            log::warning($name."はすでに登録されています。");
            return json_encode($ret_data);
        }

        // 4桁の認証コードを生成
        $pass_phrase = "";
        for ($i = 0; $i < USER_REGISTRATION_PASS_PHRASE_LENGTH; $i++) {
            $pass_phrase .= rand(0, 9);
        }

        // 仮登録処理
        $now = Carbon::now();
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
            // TODO: code_authテーブルにレコード追加、トランザクションにする
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

    // 仮登録後の認証と本登録
    public function registAuth()
    {
        $mail_address = $_POST["mail_address"];
        $pass_phrase = $_POST["pass_phrase"];

        // 仮登録ユーザ情報を、メールアドレスと認証コードで検索
        $temp_user = DB::table('temp_users')
            ->where('mail_address', $mail_address)
            ->where('pass_phrase', $pass_phrase)
            //->where('created_at', '>=', Carbon::now()->subHour()  // 仮登録の期限は1時間
            ->whereNull('deleted_at')
            ->first();

        // 仮登録を削除
        // 本登録

        return json_encode($ret_data);
    }

    // メールアドレス重複チェック
    public function isUniqueMailAddress()
    {
        $mail_address = $_POST["mail_address"];
        $ret_data["is_unique"] = $this->is_unique_mail_address($mail_address);
        return json_encode($ret_data);
    }

    // ニックネーム重複チェック
    public function isUniqueName()
    {
        $name = $_POST["name"];
        $ret_data["is_unique"] = $this->is_unique_name($name);
        return json_encode($ret_data);
    }

    // ユーザ情報バリデーション
    private function validate_user_basic_info($mail_address, $name, $password)
    {
        $err_msgs = array();

        // メールアドレス
        if ($mail_address == "")
        {
            $err_msgs[] = 'メールアドレスを入力してください。';
        }
        elseif (!preg_match("/^[a-zA-Z0-9_.+-]+[@][a-zA-Z0-9.-]+$/", $mail_address))
        {
            $err_msgs[] = 'メールアドレスに登録できない文字が含まれている、または不正なメールアドレスです。';
        }

        // ニックネーム
        if ($name == "")
        {
            $err_msgs[] = 'ニックネームを入力してください。';
        }
        elseif (mb_strlen($name) > USER_NAME_MAX_LENGTH)
        {
            $err_msgs[] = 'ニックネームは'.USER_NAME_MAX_LENGTH.'文字以内で入力してください。';
        }

        // パスワード
        if (!preg_match("/^[a-zA-Z0-9]{".USER_PASSWORD_MIN_LENGTH.",".USER_PASSWORD_MAX_LENGTH."}+$/", $password))
        {
            $err_msgs[] = 'パスワードは半角英数字' . USER_PASSWORD_MIN_LENGTH . '〜' . USER_PASSWORD_MAX_LENGTH . '文字で入力してください。';
        }
        return $err_msgs;
    }

    // メールアドレス重複チェック
    private function is_unique_mail_address($mail_address)
    {
        // ユーザ情報をDBから取得
        $user      = DB::table('users')->where('mail_address', $mail_address)->first();
        // $temp_user = DB::table('temp_users')->where('mail_address', $mail_address)->first();
        if (isset($user)/* || isset($temp_user)*/)
        {
            return false;
        }
        return true;
    }

    // ニックネーム重複チェック
    private function is_unique_name($name)
    {
        // ユーザ情報をDBから取得
        $user      = DB::table('users')->where('name', $name)->first();
        // $temp_user = DB::table('temp_users')->where('name', $name)->first();
        if (isset($user)/* || isset($temp_user)*/)
        {
            return false;
        }
        return true;
    }

}
