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

        // パラメータ取得
        $user_id    = $_POST["user_id"];
        $board_size = $_POST["board_size"];

        $data['is_success'] = '0';

        // Gameレコード新規作成
        $game_id = Game::insertGame($user_id,
                                    $user_id,
                                    $board_size,
                                    true,
                                    $user_id);

        $data['is_success'] = '1';
        $data['game_id']    = $game_id;
        return json_encode($data);
    }

    // 石を置く処理
    public function putStone()
    {
        log::debug('Api/Game/putStone');

        // 定数宣言
        $ERR_STATUS_NO_GAME        = '1';     // Gameレコードがない
        $ERR_STATUS_NO_USER_TURN   = '2';     // ユーザのターンではない
        $ERR_STATUS_CAN_NOT_PUT    = '3';     // 石を置ける場所ではない
        $ERR_STATUS_DB_EXCEPTION   = '51';    // DB更新時に例外発生
        $ERR_STATUS_UPDATE_FAILURE = '52';    // update失敗(更新レコードなし)

        // パラメータ取得
        $game_id       = $_POST["game_id"];
        $user_id       = $_POST["user_id"];
        $is_first_turn = $_POST["is_first_turn"];
        $loc_x         = $_POST["loc_x"];
        $loc_y         = $_POST["loc_y"];

        // 失敗ステータスを格納しておく
        $data['is_success'] = '0';

        // Gameレコード取得
        $game = Game::getGameById($game_id);

        // gameレコードが取得できなかった場合
        if (empty($game))
        {
            log::error("存在しないgame_id。game_id: " . $game_id);
            $data['err_status'] = $ERR_STATUS_NO_GAME;
            return json_encode($data);
        }

        // ユーザのターンであることを確認する
        $turn_user_id = $is_first_turn ? $game->user_id : $game->challenger_user_id;
        if ($turn_user_id != $user_id)
        {
            log::error("ユーザのターンではない。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $ERR_STATUS_NO_USER_TURN;
            return json_encode($data);
        }

        // 石が置けるかチェックする
        if (!Game::canPut($game, $loc_x, $loc_y))
        {
            log::error("石が置けない場所。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $ERR_STATUS_CAN_NOT_PUT;
            return json_encode($data);
        }

        // 石を置き、相手の石を裏返す
        $new_cells = Game::putStone($game, $loc_x, $loc_y);
        try
        {
            // 石を配置(cellsを更新)し、相手のターンに変更する
            $updated_num = Game::updateCellsAndTurn($game_id,
                                                    $new_cells,
                                                    !$game->turn);
        }
        catch (\Exception $e)
        {
            log::error($e);
            $data['err_status'] = $ERR_STATUS_DB_EXCEPTION;
            return json_encode($data);
        }

        // DB更新に失敗した場合(念のため)
        if ($updated_num == 0)
        {
            log::error("update失敗。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $ERR_STATUS_UPDATE_FAILURE;
            return json_encode($data);
        }
        $data['is_success'] = '1';
        return json_encode($data);
    }

    // 勝敗判定し、ゲームを終了する
    public function judge()
    {
        // 勝利判定


        // 統計情報の登録
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
