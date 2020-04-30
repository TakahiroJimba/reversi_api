<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// 「\」を入れないで使うには下記の一文を入れておくこと
use Illuminate\Support\Facades\Log;

class Game extends Model
{
    // 石の配置状況
    static public $NONE               = '0';
    static public $FIRST_COLOR_STONE  = '1';
    static public $SECOND_COLOR_STONE = '2';

    // 方向を示す配列
    static public $DIRECTION_ARRAY = null;

    static public function getGameById($game_id)
    {
        return DB::table('game')
            ->where('id', $game_id)
            ->whereNull('deleted_at')
            ->first();
    }

    // ゲーム開始時に使用
    static public function insertGame($game_mode_id,
                                      $user_id,
                                      $challenger_user_id,
                                      $board_size,
                                      $first_user,
                                      $turn_now,
                                      $turn_priority)
    {
        $now = Carbon::now();
        $game = [
            'game_mode_id'       => $game_mode_id,
            'user_id'            => $user_id,
            'challenger_user_id' => $challenger_user_id,
            'board_size'         => $board_size,
            'cells'              => Game::getInitCells($board_size),
            'first_user'         => $first_user,
            'turn_now'           => $turn_now,
            'turn_priority'      => $turn_priority,
            'created_at'         => $now,
            'updated_at'         => $now,
        ];
        return DB::table('game')->insertGetId($game);
    }

    // 石を配置(cells更新)し、相手のターンに変更する
    static public function updateCellsAndTurn($game_id,
                                              $cells,
                                              $turn_now,
                                              $play_time,
                                              $is_first)
    {
        if ($is_first)
        {
            // 更新件数を返す
            return DB::table('game')
                ->where('id', $game_id)
                ->update([
                            'cells'          => $cells,
                            'turn_now'       => $turn_now,
                            'user_play_time' => $play_time,
                            'updated_at'     => Carbon::now(),
                ]);
        }
        // 更新件数を返す
        return DB::table('game')
            ->where('id', $game_id)
            ->update([
                        'cells'                => $cells,
                        'turn_now'             => $turn_now,
                        'challenger_play_time' => $play_time,
                        'updated_at'           => Carbon::now(),
            ]);
    }

    static public function deleteGame($game_id)
    {
        DB::table('game')
            ->where('id', $game_id)
            ->delete();
    }

    // 石が置けるか確認
    static public function canPut($game, $loc_x, $loc_y)
    {
        $board_size = $game->board_size;
        $put_target_cell = $game->cells[$loc_y + $loc_x * $board_size];
        if ($put_target_cell != Game::$NONE)
        {
            // 既に石が置かれている
            return false;
        }
        // 全方向について、相手の石を挟んでいるかチェックする
        return Game::watchLine($game, $loc_x, $loc_y);
    }

    // 石を置き、相手の石を裏返す
    static public function putStone($game, $x, $y)
    {
        $cells = $game->cells;
        $stone_number = $game->turn_now ? Game::$FIRST_COLOR_STONE : Game::$SECOND_COLOR_STONE;

        // 石を置く
        $cells[$y + $x * $game->board_size] = $stone_number;

        // 相手の石を裏返す
        for ($i = 0; $i < count(Game::$DIRECTION_ARRAY); $i++)
        {
            // ある方向の挟んでいる石の数を取得
            $turn_over_count = Game::turnOverCount($game, $x, $y, Game::$DIRECTION_ARRAY[$i], $stone_number);
            for($step = 1; $step <= $turn_over_count; $step++)
            {
                $now_x = $x + $step * Game::$DIRECTION_ARRAY[$i]['x'];
                $now_y = $y + $step * Game::$DIRECTION_ARRAY[$i]['y'];
                $cells[$now_y + $now_x * $game->board_size] = $stone_number;
            }
        }
        return $cells;
    }

    // 石を置ける場所がなければtrueを返す
    static public function isGameEnd($game)
    {
        $board_size = $game->board_size;
        for($y = 0; $y < $board_size; $y++)
        {
            for($x = 0; $x < $board_size; $x++)
            {
                if($game->cells[$y + $x * $board_size] != Game::$NONE)
                {
                    continue;
                }
                if(Game::canPut($x, $y, Game::$FIRST_COLOR_STONE) || Game::canPut($x, $y, Game::$SECOND_COLOR_STONE))
                {
                    // 石を置ける場所がある
                    return false;
                }
            }
        }
        // 石を置ける場所がない
        return true;
    }

    // 両プレイヤーの石を数える
    static public function countStones($game)
    {
        $stone_nums['first']  = 0;
        $stone_nums['second'] = 0;
        for($y = 0; $y < $game->board_size; $y++)
        {
            for($x = 0; $x < $game->board_size; $x++)
            {
                if ($game->cells[$y + $x * $game->board_size] == Game::$FIRST_COLOR_STONE)
                {
                    $stone_nums['first']++;
                }
                elseif ($game->cells[$y + $x * $game->board_size] == Game::$SECOND_COLOR_STONE)
                {
                    $stone_nums['second']++;
                }
            }
        }
        return $stone_nums;
    }

    // 初期状態の石の配置を一次元配列で取得する
    static private function getInitCells($board_size)
    {
        $cells = "";
        for ($i = 0; $i < $board_size * $board_size; $i++)
        {
            $cells .= Game::$NONE;  // 文字列なので注意
        }
        $center_loc = $board_size / 2;

        $x = $center_loc - 1;
        $y = $center_loc - 1;
        $cells[$y + $x * $board_size] = Game::$FIRST_COLOR_STONE;

        $x = $center_loc;
        $y = $center_loc;
        $cells[$y + $x * $board_size] = Game::$FIRST_COLOR_STONE;

        $x = $center_loc;
        $y = $center_loc - 1;
        $cells[$y + $x * $board_size] = Game::$SECOND_COLOR_STONE;

        $x = $center_loc - 1;
        $y = $center_loc;
        $cells[$y + $x * $board_size] = Game::$SECOND_COLOR_STONE;
        return $cells;
    }

    static private function watchLine($game, $x, $y)
    {
        $stone_number = $game->turn_now ? Game::$FIRST_COLOR_STONE : Game::$SECOND_COLOR_STONE;

        Game::initDirectionArray();

        // 各方向について処理
        $turn_over_count = 0;
        for ($i = 0; $i < count(Game::$DIRECTION_ARRAY); $i++)
        {
            $turn_over_count += Game::turnOverCount($game, $x, $y, Game::$DIRECTION_ARRAY[$i], $stone_number);
        }
        if ($turn_over_count == 0)
        {
            // 全方向について、相手の石を挟んでいない
            return false;
        }
        // 挟んでいる石がある
        return true;
    }

    // 引数の方向について、挟んでいる相手の石の数を返す
    static private function turnOverCount($game, $x, $y, $d, $stone_number)
    {
        $d_x = $d['x'];
        $d_y = $d['y'];
        $step = 1;      // いくつ隣か
        $exist_opponent_stone = false;   // 相手の石を挟んでいるか
        $board_size = $game->board_size;

        while (Game::inBoardRange($x + $step * $d_x, $y + $step * $d_y, $board_size))
        {
            $location = $game->cells[($y + $step * $d_y) + ($x + $step * $d_x) * $board_size];
            if ($location == Game::$NONE)
            {
                break;
            }
            if ($exist_opponent_stone)
            {
                // 既に相手の石を挟んでいる
                if ($location == $stone_number)
                {
                    // 自分の石だった場合
                    return $step - 1;
                }
            }
            else
            {
                // まだ相手の石を挟んでいない
                if ($location != $stone_number)
                {
                    // 相手の石だった場合
                    $exist_opponent_stone = true;
                }
                else
                {
                    return 0;
                }
            }
            $step++;
        }
        return 0;
    }

    // 盤の範囲内であればtrueを返す
    static private function inBoardRange($x, $y, $board_size)
    {
        if ($x < 0 || $x >= $board_size)
        {
            return false;
        }
        if ($y < 0 || $y >= $board_size)
        {
            return false;
        }
        return true;
    }

    // 方向定数の初期化
    static private function initDirectionArray()
    {
        if (!empty(Game::$DIRECTION_ARRAY))
        {
            // 既に初期化されているので処理不要
            return;
        }
        Game::$DIRECTION_ARRAY = array();
        for($x = -1; $x <= 1; $x++)
        {
            for($y = -1; $y <= 1; $y++)
            {
                if($x == 0 && $y == 0)
                {
                    continue;
                }
                $temp_d['x'] = $x;
                $temp_d['y'] = $y;
                Game::$DIRECTION_ARRAY[] = $temp_d;
            }
        }
    }

    // // 石を一次元配列にセットする
    // static private function setStone($cells, $board_size, $x, $y, $stone_color)
    // {
    //     $cells[$y + $x * $board_size] = $stone_color;
    // }
}
