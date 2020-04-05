<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Http\Controllers\Controller;    // ディレクトリ階層が別な場合に必要

// 「\」を入れないで使うには下記の一文を入れておくこと
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    // ルーム作成
    public function create()
    {
        $name = $_POST["name"];
        $password = $_POST["password"];

        // ルーム作成に失敗した場合、room_idは空白を返す
        $data["room_id"] = "1";
        return json_encode($data);
    }

    // マッチング確認
    public function watch()
    {
        // マッチングに失敗した場合、game_idは空白を返す
        $data["game_id"] = "1";
        return json_encode($data);
    }

    // ルーム入場
    public function enter()
    {

    }
}
