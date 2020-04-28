<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Game extends Model
{
    static public function insertGame($user_id,
                                      $challenger_user_id,
                                      $board_size,
                                      $turn,
                                      $turn_priority)
    {
        $now = Carbon::now();
        $game = [
            'user_id'            => $user_id,
            'challenger_user_id' => $challenger_user_id,
            'board_size'         => $board_size,
            'cells'              => Game::getInitCells($board_size),
            'turn'               => $turn,
            'turn_priority'      => $turn_priority,
            'created_at'         => $now,
            'updated_at'         => $now,
        ];
        return DB::table('game')->insertGetId($game);
    }

    // 初期状態の石の配置を一次元配列で取得する
    static private function getInitCells($board_size)
    {
        $NONE = '0';
        $FIRST_COLOR_STONE = '1';
        $SECOND_COLOR_STONE = '2';

        $cells = "";
        for ($i = 0; $i < $board_size * $board_size; $i++)
        {
            $cells .= $NONE;
        }
        $center_loc = $board_size / 2;

        $x = $center_loc - 1;
        $y = $center_loc - 1;
        $cells[$y + $x * $board_size] = $FIRST_COLOR_STONE;

        $x = $center_loc;
        $y = $center_loc;
        $cells[$y + $x * $board_size] = $FIRST_COLOR_STONE;

        $x = $center_loc;
        $y = $center_loc - 1;
        $cells[$y + $x * $board_size] = $SECOND_COLOR_STONE;

        $x = $center_loc - 1;
        $y = $center_loc;
        $cells[$y + $x * $board_size] = $SECOND_COLOR_STONE;
        return $cells;
    }

    // // 石を一次元配列にセットする
    // static private function setStone($cells, $board_size, $x, $y, $stone_color)
    // {
    //     $cells[$y + $x * $board_size] = $stone_color;
    // }
}
