<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Http\Controllers\Controller;    // ディレクトリ階層が別な場合に必要

// 「\」を入れないで使うには下記の一文を入れておくこと
use Illuminate\Support\Facades\Log;
use App\Model\Game;

class GameController extends Controller
{
    // オフライン用のGameテーブルレコードを作成
    public function createOfflineGame()
    {
        log::debug('Api/Game/createOfflineGame');

        $user_id    = $_POST["user_id"];
        $board_size = $_POST["board_size"];

        $data['is_success'] = false;

        // レコード新規作成
        $game_id = Game::insertGame($user_id,
                                    $user_id,
                                    $board_size,
                                    true,
                                    $user_id);

        $data['is_success'] = '1';
        $data['game_id']    = $game_id;
        return json_encode($data);
    }


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
