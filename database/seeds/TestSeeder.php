<?php

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // マスタ系
        $this->call(ResultsTableSeeder::class);
        $this->call(GameModeTableSeeder::class);

        // --- テスト時に必要なデータを登録する ---

    }
}
