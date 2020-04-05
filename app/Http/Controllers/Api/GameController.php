<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Http\Controllers\Controller;    // ディレクトリ階層が別な場合に必要

// 「\」を入れないで使うには下記の一文を入れておくこと
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    // 優先権確認
    public function getPriority()
    {
        // 優先権を取得して返す
        $data["priority"] = true;
        return json_encode($data);
    }

    // ターン設定
    public function setTurn()
    {
        
    }
}
