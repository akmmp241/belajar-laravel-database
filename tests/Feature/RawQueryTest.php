<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    public function testCRUD()
    {
        DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
            "GADGET", "Gadget", "Gadget Catageogry", "2020-10-10 10:10:10"
        ]);

        $results = DB::select("select * from categories where id = ?", ['GADGET']);

        self::assertCount(1, $results);
        self::assertEquals("GADGET", $results[0]->id);
        self::assertEquals("Gadget", $results[0]->name);
        self::assertEquals("Gadget Catageogry", $results[0]->description);
        self::assertEquals("2020-10-10 10:10:10", $results[0]->created_at);
    }

    public function testCRUDNamedParameter()
    {
        DB::insert("insert into categories (id, name, description, created_at) values (:id, :name, :description, :created_at)", [
            "id" => "GADGET",
            "name" => "Gadget",
            "description" => "Gadget Catageogry",
            "created_at" => "2020-10-10 10:10:10"
        ]);

        $results = DB::select("select * from categories where id = ?", ['GADGET']);

        self::assertCount(1, $results);
        self::assertEquals("GADGET", $results[0]->id);
        self::assertEquals("Gadget", $results[0]->name);
        self::assertEquals("Gadget Catageogry", $results[0]->description);
        self::assertEquals("2020-10-10 10:10:10", $results[0]->created_at);
    }
}
