<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('delete from products');
        DB::delete("DELETE FROM categories");
        DB::delete('delete from counters');
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
        $this->seed(CounterSeeder::class);
        DB::table('counters')->where('id', '=', 'SAMPLE')->increment('counter', 1);

        $collection = DB::table('counters')->where('id', '=', 'SAMPLE')->get();
        assertCount(1, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testDelete()
    {
        $this->insertCategories();

        DB::table('categories')->where('id', '=', 'SMARTPHONE')->delete();

        $collection = DB::table('categories')->where('id', '=', 'SMARTPHONE')->get();

        assertCount(0, $collection);
        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testJoin()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'categories.name as category_name', 'products.price')
            ->get();

        assertCount(4, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testOrderBy()
    {
        $this->insertProducts();

        $collection = DB::table('products')->whereNotNull('id')
            ->orderBy('price', 'asc')
            ->get();

        assertCount(4, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testPaging()
    {
        $this->insertCategories();

        $collection = DB::table('categories')
            ->take(2)
            ->skip(2)
            ->get();

        assertCount(2, $collection);

        $collection->map(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testChunk()
    {
        $this->insertManyCategories();

        DB::table('categories')->orderBy('id')
            ->chunk(10, function ($categories) {
                assertCount(10, $categories);
                Log::alert('start chunk');
                $categories->map(function ($item) {
                    Log::info(json_encode($item));
                });
                Log::alert('end chuck');
            });
    }

    public function testLazy()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')->orderBy('id')
            ->lazy(10)->take(2);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });

        self::assertTrue(true);
    }

    public function testCursor()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')->orderBy('id')->cursor();

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });

        self::assertTrue(true);
    }

    public function testGroupBy()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->select('category_id', DB::raw("count(*) as total_product"))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->get();

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });

        assertCount(2, $collection);
        self::assertEquals('SMARTPHONE', $collection[0]->category_id);
        self::assertEquals('FOOD', $collection[1]->category_id);
        assertEquals(2, $collection[0]->total_product);
        assertEquals(2, $collection[1]->total_product);
    }

    public function testLocking()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->where('id', '=', 1)
            ->lockForUpdate()
            ->get();

        assertCount(1, $collection);
    }

    public function testPagination()
    {
        $this->insertManyCategories();

        $paginate = DB::table('categories')->paginate(perPage: 5, page: 14);

        assertEquals(14, $paginate->currentPage());
        assertEquals(5, $paginate->perPage());
        assertEquals(20, $paginate->lastPage());
        assertEquals(100, $paginate->total());

        $collection = $paginate->items();
        assertCount(5, $collection);

        foreach ($collection as $item) {
            Log::info(json_encode($item));
        }

    }

    public function testIterateAllPagination()
    {
        $this->insertManyCategories();

        $page = 1;
        while (true) {
            $paginate = DB::table('categories')->paginate(5, page: $page);

            if ($paginate->isEmpty()) {
                break;
            } else {
                $collection = $paginate->items();
                assertCount(5, $collection);
                foreach ($collection as $item) {
                    Log::info(json_encode($item));
                }
                $page++;
            }
        }

    }


    public function testGroupByHaving()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->select('category_id', DB::raw("count(*) as total_product"))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->having(DB::raw('count(*)'), '>', 2)
            ->get();

        assertCount(0, $collection);
    }

    public function testCursorPagination()
    {
        $this->insertManyCategories();

        $cursor = "id";
        while (true) {
            $paginate = DB::table('categories')->orderBy("id")->cursorPaginate(perPage: 5, cursor: $cursor);

            foreach ($paginate->items() as $item) {
                self::assertNotNull($item);
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }


    public function insertCategories(): void
    {
        $this->seed(CategorySeeder::class);
    }

    public function insertProducts(): void
    {
        $this->insertCategories();

        DB::table('products')->insert([
            "id" => "1",
            "name" => "Iphone 14 Pro Max",
            "category_id" => "SMARTPHONE",
            "price" => 20000000
        ]);
        DB::table('products')->insert([
            "id" => "2",
            "name" => "Samsung Galaxy S24 Ultra",
            "category_id" => "SMARTPHONE",
            "price" => 18000000
        ]);
        DB::table('products')->insert([
            "id" => "3",
            "name" => "Bakso",
            "category_id" => "FOOD",
            "price" => 20000
        ]);
        DB::table('products')->insert([
            "id" => "4",
            "name" => "Mie Ayam",
            "category_id" => "FOOD",
            "price" => 20000
        ]);
    }

    public function insertManyCategories(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            DB::table('categories')
                ->insert([
                    "id" => "CATEGORIES-$i",
                    "name" => "Category $i",
                    "description" => "",
                    "created_at" => "2020-10-10 10:10:10"
                ]);
        }
    }
}
