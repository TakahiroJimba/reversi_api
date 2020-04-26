<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ResultsTableSeeder extends Seeder
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
        $TABLE_NAME = 'results';

        // データを全削除
        // 参照しているテーブルから削除する
        DB::table($TABLE_NAME)->delete();

        $data = [
            [
                'id'         => RESULT_ID_WIN,
                'name'       => "勝ち",
                'name_en'    => 'win',
            ],
            [
                'id'         => RESULT_ID_LOSE,
                'name'       => "負け",
                'name_en'    => 'lose',
            ],
            [
                'id'         => RESULT_ID_DRAW,
                'name'       => "引き分け",
                'name_en'    => 'draw',
            ],
            [
                'id'         => RESULT_ID_SURRENDER,
                'name'       => "降参負け",
                'name_en'    => 'surrender(lose)',
            ],
            [
                'id'         => RESULT_ID_OPPONENT_SURRENDER,
                'name'       => "相手の降参",
                'name_en'    => 'surrender(win)',
            ],
            [
                'id'         => RESULT_ID_DISCONNECT,
                'name'       => "切断負け",
                'name_en'    => 'disconnect(lose)',
            ],
            [
                'id'         => RESULT_ID_OPPONENT_DISCONNECT,
                'name'       => "相手の切断",
                'name_en'    => 'disconnect(win)',
            ],
        ];
        DB::table($TABLE_NAME)->insert($data);
    }
}
