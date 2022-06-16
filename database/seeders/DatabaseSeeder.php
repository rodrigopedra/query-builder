<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Preparation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create();

        Dish::factory()->count(10)->create();
        Preparation::factory()->count(10)->create();

        $excludedDishRus = Dish::factory()->create(['name' => 'Тест 1']);
        $excludedPreparationRus = Preparation::factory()->create(['name' => 'Тест 2']);
        $excludedDishEn = Dish::factory()->create(['name' => 'Test 1']);
        $excludedPreparationEn = Preparation::factory()->create(['name' => 'Test 2']);

        $user->excludedItems()->create(['item_id' => $excludedDishRus->id, 'item_type' => 'dish']);
        $user->excludedItems()->create(['item_id' => $excludedPreparationRus->id, 'item_type' => 'preparation']);
        $user->excludedItems()->create(['item_id' => $excludedDishEn->id, 'item_type' => 'dish']);
        $user->excludedItems()->create(['item_id' => $excludedPreparationEn->id, 'item_type' => 'preparation']);
    }
}
