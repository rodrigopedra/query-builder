<?php

use App\Models\Dish;
use App\Models\Preparation;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('eloquent', function () {
    $searchString = '%тест%';

    // baseline: proof MySQL works with search string
    $dishes = Dish::query()->where('name', 'like', $searchString)->get();
    \dump($dishes->toArray());

    // test
    $dishesQuery = Dish::query()
        ->select(['dishes.id', 'dishes.name', DB::raw("'dish' as item_type")]);

    $preparationsQuery = Preparation::query()
        ->select(['preparations.id', 'preparations.name', DB::raw("'preparation' as item_type")]);

    $itemsQuery = $dishesQuery->union($preparationsQuery);

    $user = User::query()->find(1);

    $builder = $user->excludedItems()
        ->joinSub($itemsQuery, 'items', function (JoinClause $join) {
            $join->on('items.id', 'excluded_items.item_id')
                ->on('items.item_type', 'excluded_items.item_type');
        })
        ->select(['excluded_items.*', 'items.name'])
        ->where('items.name', 'like', $searchString);

    $statement = $builder->getConnection()->getPdo()->prepare($builder->toSql());
    $statement->execute([1, $searchString]);

    \dd(
        $statement->fetchAll(\PDO::FETCH_ASSOC), // has results
        $builder->get()->toArray(), // empty results
    );
});
