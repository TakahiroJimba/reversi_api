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
use App\Model\History;

class GameController extends Controller
{
    // 定数宣言
    private $ERR_STATUS_NO_GAME        = '1';     // Gameレコードがない
    private $ERR_STATUS_NO_USER_TURN   = '2';     // ユーザのターンではない
    private $ERR_STATUS_CAN_NOT_PUT    = '3';     // 石を置ける場所ではない
    private $ERR_STATUS_CAN_PUT_SPACE  = '4';     // 石を置ける場所がまだある
    private $ERR_STATUS_DB_EXCEPTION   = '51';    // DB更新時に例外発生
    private $ERR_STATUS_UPDATE_FAILURE = '52';    // update失敗(更新レコードなし)

    // private $RESULT_STATUS_WIN         = '1';     // プレイヤーの勝ち
    // private $RESULT_STATUS_LOSE        = '-1';    // プレイヤーの負け
    // private $RESULT_STATUS_DRAW        = '0';     // 引き分け

    // オフライン用のGameテーブルレコードを作成
    public function createOfflineGame()
    {
        log::debug('Api/Game/createOfflineGame');

        // パラメータ取得
        $user_id    = $_POST["user_id"];
        $board_size = $_POST["board_size"];

        $data['is_success'] = '0';

        // board_sizeが偶数か確認する
        if ($board_size % 2 != 0)
        {
            log::error("board_sizeが偶数でない。board_size: " . $board_size);
            return json_encode($data);
        }

        // Gameレコード新規作成
        $game_id = Game::insertGame(GAME_MODE_ID_OFFLINE,
                                    $user_id,
                                    $user_id,
                                    $board_size,
                                    $user_id,
                                    true,
                                    $user_id);

        $data['is_success'] = '1';
        $data['game_id']    = $game_id;
        return json_encode($data);
    }

    // 石を置く処理
    public function putStone()
    {
        // logが肥大化するので出力しない
        //log::debug('Api/Game/putStone');

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
            $data['err_status'] = $this->ERR_STATUS_NO_GAME;
            return json_encode($data);
        }

        // ユーザのターンであることを確認する
        $turn_user_id = $is_first_turn ? $game->user_id : $game->challenger_user_id;
        if ($turn_user_id != $user_id)
        {
            log::error("ユーザのターンではない。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $this->ERR_STATUS_NO_USER_TURN;
            return json_encode($data);
        }

        // 石が置けるかチェックする
        if (!Game::canPut($game, $loc_x, $loc_y, $game->turn_now))
        {
            log::error("石が置けない場所。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $this->ERR_STATUS_CAN_NOT_PUT;
            return json_encode($data);
        }

        // 石を置き、相手の石を裏返す
        $game->cells = Game::putStone($game, $loc_x, $loc_y);

        // 次のターンプレイヤーの石を置ける場所がなければ、ターンチェンジしない
        $next_turn = Game::canPutSpace($game, !$game->turn_now) ? !$game->turn_now : $game->turn_now;

        // 消費時間(秒数)を計算する
        $diff_play_time_seconds = Carbon::now()->diffInSeconds(new Carbon($game->updated_at));
        $play_time = $game->turn_now ? $game->user_play_time : $game->challenger_play_time;
        $play_time = (new Carbon($play_time))->addSeconds($diff_play_time_seconds)->timestamp;

        try
        {
            // 石を配置(cellsを更新)し、相手の石が置ける場合はターンチェンジする
            $updated_num = Game::updateCellsAndTurn($game_id,
                                                    $game->cells,
                                                    $next_turn, // !$game->turn_now,
                                                    $play_time,
                                                    $game->turn_now);
        }
        catch (\Exception $e)
        {
            log::error($e);
            $data['err_status'] = $this->ERR_STATUS_DB_EXCEPTION;
            return json_encode($data);
        }

        // DB更新に失敗した場合(念のため)
        if ($updated_num == 0)
        {
            log::error("update失敗。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $this->ERR_STATUS_UPDATE_FAILURE;
            return json_encode($data);
        }
        $data['is_success'] = '1';
        return json_encode($data);
    }

    // 勝敗判定し、ゲームを終了する
    public function judge()
    {
        log::debug('Api/Game/judge');

        // パラメータ取得
        $game_id       = $_POST["game_id"];
        $user_id       = $_POST["user_id"];
        $is_first_turn = $_POST["is_first_turn"];
        $is_surrender  = $_POST["is_surrender"];

        // 失敗ステータスを格納しておく
        $data['is_success'] = '0';

        // Gameレコード取得
        $game = Game::getGameById($game_id);

        // gameレコードが取得できなかった場合
        if (empty($game))
        {
            log::error("存在しないgame_id。game_id: " . $game_id);
            $data['err_status'] = $this->ERR_STATUS_NO_GAME;
            return json_encode($data);
        }

        // ユーザのターンであることを確認する
        $turn_user_id = $is_first_turn ? $game->user_id : $game->challenger_user_id;
        if ($turn_user_id != $user_id)
        {
            log::error("ユーザのターンではない。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $this->ERR_STATUS_NO_USER_TURN;
            return json_encode($data);
        }

        // 降参の場合
        if ($is_surrender)
        {
            $data['result_id'] = RESULT_ID_SURRENDER;
        }

        // 両者ともに石を置ける場所がないことを確認する
        if (!Game::isGameEnd($game))
        {
            // 石を置ける場所がある
            log::error("石を置ける場所がまだある。game_id: " . $game_id . ", user_id: " . $user_id);
            $data['err_status'] = $this->$ERR_STATUS_CAN_PUT_SPACE;
            return json_encode($data);
        }

        // 両プレイヤーの石を数える
        $stone_nums = Game::countStones($game);

        // 勝敗判定
        if ($stone_nums['first'] == $stone_nums['second'])
        {
            $data['result_id'] = RESULT_ID_DRAW;
        }
        // 先行プレイヤーの勝ち
        elseif ($stone_nums['first'] > $stone_nums['second'])
        {
            $data['result_id'] = $game->first_user == $game->user_id ? RESULT_ID_WIN : RESULT_ID_LOSE;
        }
        // 後攻プレイヤーの勝ち
        else
        {
            $data['result_id'] = $game->first_user == $game->user_id ? RESULT_ID_LOSE : RESULT_ID_WIN;
        }

        // トランザクション処理はしない
        // DB::beginTransaction();
        try
        {
            // 対戦履歴を登録する
            History::insertHistory($game, $stone_nums, $data['result_id']);

            // Gameレコードを削除する
            Game::deleteGame($game_id);
            // DB::commit();
        }
        catch (\Exception $e)
        {
            // DB::rollback();
            log::error($e);
            $data['err_status'] = $this->ERR_STATUS_DB_EXCEPTION;
            return json_encode($data);
        }
        // 処理成功
        $data['is_success'] = '1';
        return json_encode($data);
    }

    // ボードの状態を初期化し、ゲームを最初からやり直す
    public function restartGame()
    {
        log::debug('Api/Game/restartGame');

        // パラメータ取得
        $game_id = $_POST["game_id"];

        $data['is_success'] = '0';

        // board_sizeが偶数か確認する
        if ($board_size % 2 != 0)
        {
            log::error("board_sizeが偶数でない。board_size: " . $board_size);
            return json_encode($data);
        }

        // Gameレコード新規作成
        $game_id = Game::insertGame(GAME_MODE_ID_OFFLINE,
                                    $user_id,
                                    $user_id,
                                    $board_size,
                                    $user_id,
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
