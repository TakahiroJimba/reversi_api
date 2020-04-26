<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class GameModeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        require_once app_path() . '/config/const.php';

        // テーブル名
        $TABLE_NAME = 'game_mode';

        // データを全削除
        // 参照しているテーブルから削除する
        DB::table($TABLE_NAME)->delete();

        $data = [
            [
                'id'         => GAME_MODE_ID_OFFLINE,
                'name'       => "オフライン対戦",
                'name_en'    => 'OFFLINE',
            ],
            [
                'id'         => GAME_MODE_ID_AI,
                'name'       => "AI対戦",
                'name_en'    => 'vs AI',
            ],
            [
                'id'         => GAME_MODE_ID_ONLINE_ROOM,
                'name'       => "オンライン(部屋)",
                'name_en'    => 'ONLINE(ROOM)',
            ],
            [
                'id'         => GAME_MODE_ID_ONLINE_FREE,
                'name'       => "オンライン(フリー対戦)",
                'name_en'    => 'ONLINE(FREE)',
            ],
        ];
        DB::table($TABLE_NAME)->insert($data);
    }
}
