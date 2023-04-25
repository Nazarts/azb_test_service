<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('positions')->insert([
            ['name' => 'Junior Software Developer'],
            ['name' => 'Senior Software Developer'],
            ['name' => 'Middle Software Developer'],
            ['name' => 'Junior Data Analyst'],
            ['name' => 'Junior Business Analyst'],
            ['name' => 'Product Manager']
        ]);
    }
}
