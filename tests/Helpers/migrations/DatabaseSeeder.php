<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('testbench')
            ->table('cms_test_records')
            ->insert([
                'id'          => 1,
                'description' => 'testing!',
                'created_at'  => '2017-01-01 00:00:00',
                'updated_at'  => '2017-01-01 00:00:00',
            ]);
    }

}
