<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Http\Controllers\Controller;    // ディレクトリ階層が別な場合に必要

// 「\」を入れないで使うには下記の一文を入れておくこと
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    // ログイン認証
    public function auth()
    {
        $id = $_POST["id"];
        $password = $_POST["password"];

        // TODO: ログイン認証

        if (strval($password) == "password")
        {
            $is_login = "1";
        }
        else
        {
            // TODO: エラーログ出力
            $is_login =  "0";
        }
        $data["is_login"] = $is_login;
        return json_encode($data);
    }
}
