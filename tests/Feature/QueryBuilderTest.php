<?php

namespace Tests\Feature;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use function PHPUnit\Framework\assertCount;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    public function testInsert()
    {
        DB::table('categories')->insert([
            "id" => "GADGET",
            "name" => "Gadget",
            "description" => "Gadget Category",
            "created_at" => "2020-10-10 10:10:10"
        ]);
        DB::table('categories')->insert([
            "id" => "FOOD",
            "name" => "Food",
            "description" => "Food Category",
            "created_at" => "2020-10-10 10:10:10"
        ]);

        $result = DB::select("select count(id) as total from categories");
        self::assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table('categories')->select('id', 'name')->get();
        self::assertNotNull($collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhere()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->where(function (Builder $builder) {
            $builder->where('id', '=', 'SMARTPHONE');
            $builder->orWhere('id', '=', 'LAPTOP');
        })->get();
        self::assertCount(2, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereBetween()
    {
        $this->insertCategories();

        $collection = DB::table('categories')
            ->whereBetween('created_at', ["2020-09-10 10:10:10", "2020-11-10 10:10:10"])->get();
        assertCount(4, $collection);
        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insertCategories();

        $collection = DB::table('categories')
            ->whereIn('id', ['SMARTPHONE', 'LAPTOP'])->get();

        assertCount(2, $collection);
        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategories();

        $collection = DB::table('categories')
            ->whereDate('created_at', '2020-10-10')->get();

        assertCount(4, $collection);
        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->insertCategories();

        DB::table('categories')->where('id', '=', 'SMARTPHONE')
            ->update(['name' => 'Handphone']);

        $collection = DB::table('categories')->where('name', '=', 'Handphone')->get();
        assertCount(1, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpsert()
    {
        DB::table('categories')->updateOrInsert([
            "id" => "VOUCHER"
        ], [
            "name" => "Voucher",
        ]);

        $collection = DB::table('categories')->where('id', '=', 'VOUCHER')->get();
        assertCount(1, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->testUpsert();

        $collection = DB::table('categories')->whereNull('description')->get();

        assertCount(1, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testIncrement()
    {
        DB::table('counters')->where('id', '=', 'SAMPLE')->increment('counter', 1);

        $collection = DB::table('counters')->where('id', '=', 'SAMPLE')->get();
        assertCount(1, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }


    public function insertCategories()
    {
        DB::table('categories')->insert([
            "id" => "FOOD",
            "name" => "Food",
            "description" => "Food Category",
            "created_at" => "2020-10-10 10:10:10"
        ]);
        DB::table('categories')->insert([
            "id" => "SMARTPHONE",
            "name" => "Smartphone",
            "description" => "Smartphone Category",
            "created_at" => "2020-10-10 10:10:10"
        ]);
        DB::table('categories')->insert([
            "id" => "LAPTOP",
            "name" => "Laptop",
            "description" => "Laptop Category",
            "created_at" => "2020-10-10 10:10:10"
        ]);
        DB::table('categories')->insert([
            "id" => "FASHION",
            "name" => "Fashion",
            "description" => "Fashion Category",
            "created_at" => "2020-10-10 10:10:10"
        ]);
    }
}
