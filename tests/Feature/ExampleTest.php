<?php

namespace Tests\Feature;

use App\Models\{Dish, ExcludedItem, Preparation, User};
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function testThisPassesWithMatchingCaseAndChangedWheres()
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

        $dishesQuery = Dish::query()
            ->select(['dishes.id', 'dishes.name', DB::raw("'dish' as item_type")])
            ->where('dishes.name', 'like', '%Тест%');

        $preparationsQuery = Preparation::query()
            ->select(['preparations.id', 'preparations.name', DB::raw("'preparation' as item_type")])
            ->where('preparations.name', 'like', '%Тест%');

        $itemsQuery = $dishesQuery->union($preparationsQuery);

        $countWithoutFilter = $user->excludedItems()
            ->joinSub($itemsQuery, 'items', function (JoinClause $join) {
                $join->on('items.id', 'excluded_items.item_id')
                    ->on('items.item_type', 'excluded_items.item_type');
            })
            ->select(['excluded_items.*', 'items.name'])
            ->count();

        $this->assertEquals(2, $countWithoutFilter);
    }

    public function testThisPassesWithMismatchingCaseAndChangedWheres()
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

        $dishesQuery = Dish::query()
            ->select(['dishes.id', 'dishes.name', DB::raw("'dish' as item_type")])
            ->where('dishes.name', 'like', '%тест%');

        $preparationsQuery = Preparation::query()
            ->select(['preparations.id', 'preparations.name', DB::raw("'preparation' as item_type")])
            ->where('preparations.name', 'like', '%тест%');

        $itemsQuery = $dishesQuery->union($preparationsQuery);

        $countWithoutFilter = $user->excludedItems()
            ->joinSub($itemsQuery, 'items', function (JoinClause $join) {
                $join->on('items.id', 'excluded_items.item_id')
                    ->on('items.item_type', 'excluded_items.item_type');
            })
            ->select(['excluded_items.*', 'items.name'])
            ->count();

        $this->assertEquals(2, $countWithoutFilter);
    }

    public function testThisPassesWithMismatchingCaseAndNoUser()
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

        $dishesQuery = Dish::query()
            ->select(['dishes.id', 'dishes.name', DB::raw("'dish' as item_type")]);

        $preparationsQuery = Preparation::query()
            ->select(['preparations.id', 'preparations.name', DB::raw("'preparation' as item_type")]);

        $itemsQuery = $dishesQuery->union($preparationsQuery);

        $countWithoutFilter = ExcludedItem::query()
            ->joinSub($itemsQuery, 'items', function (JoinClause $join) {
                $join->on('items.id', 'excluded_items.item_id')
                    ->on('items.item_type', 'excluded_items.item_type');
            })
            ->select(['excluded_items.*', 'items.name'])
            ->where('items.name', 'like', '%тест%')
            ->count();

        $this->assertEquals(2, $countWithoutFilter);
    }
}
