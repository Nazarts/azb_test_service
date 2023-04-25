<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PositionSeeder::class
        ]);

        $position_ids = Position::all()->pluck('id')->toArray();

        User::factory()->count(45)
            ->sequence(fn(Sequence $sequence) => [
                'position_id' => $position_ids[array_rand($position_ids)]
            ])
            ->create();

    }
}
