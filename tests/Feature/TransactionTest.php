<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Catageogry", "2020-10-10 10:10:10"
            ]);
            DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                "FOOD", "Food", "Food Catageogry", "2020-10-10 10:10:10"
            ]);
        });

        $results = DB::select("select * from categories");
        self::assertCount(2, $results);
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                    "GADGET", "Gadget", "Gadget Catageogry", "2020-10-10 10:10:10"
                ]);
                DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                    "GADGET", "Gadget", "Gadget Catageogry", "2020-10-10 10:10:10"
                ]);
            });
        } catch (QueryException $exception) {
            // exception
        }

        $results = DB::select("select * from categories");
        self::assertCount(0, $results);
    }

    public function testManualTransactionSuccess()
    {
        try {
            DB::beginTransaction();
            DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Catageogry", "2020-10-10 10:10:10"
            ]);
            DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                "FOOD", "Food", "Food Catageogry", "2020-10-10 10:10:10"            ]);
            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();
        }

        $results = DB::select("select * from categories");
        self::assertCount(2, $results);
    }

    public function testManualTransactionFailed()
    {
        try {
            DB::beginTransaction();
            DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Catageogry", "2020-10-10 10:10:10"
            ]);
            DB::insert("insert into categories (id, name, description, created_at) values (?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Catageogry", "2020-10-10 10:10:10"
            ]);
            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();
        }

        $results = DB::select("select * from categories");
        self::assertCount(0, $results);
    }
}
