<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class History extends Model
{
    static public function insertHistory($game, $stone_nums, $result_id)
    {
        // 先行かどうか
        $is_first = $game->first_user == $game->user_id;
        $my_stone_num       =  $is_first ? $stone_nums['first'] : $stone_nums['second'];
        $opponent_stone_num = !$is_first ? $stone_nums['first'] : $stone_nums['second'];

        $now = Carbon::now();
        $data = [
            'user_id'            => $game->user_id,
            'opponent_user_id'   => $game->challenger_user_id,
            'game_mode_id'       => $game->game_mode_id,
            'board_size'         => $game->board_size,
            'cells'              => $game->cells,
            'my_stone_num'       => $my_stone_num,
            'opponent_stone_num' => $opponent_stone_num,
            'result_id'          => $result_id,
            'is_first'           => $is_first,
            'game_total_time'    => $game->user_play_time + $game->challenger_play_time,
            'your_game_time'     => $game->user_play_time,
            'created_at'         => $now,
            'updated_at'         => $now,
        ];
        DB::table('history')->insert($data);
    }
}
