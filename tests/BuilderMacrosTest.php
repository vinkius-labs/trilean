<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class BuilderMacrosTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('builder_macro_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('verified')->nullable();
            $table->string('blocked')->nullable();
            $table->string('reviewed')->nullable();
            $table->timestamps();
        });

        BuilderMacroUser::unguard();

        BuilderMacroUser::create(['name' => 'True User', 'verified' => 'yes', 'blocked' => 'no', 'reviewed' => 'yes']);
        BuilderMacroUser::create(['name' => 'False User', 'verified' => 'no', 'blocked' => 'yes', 'reviewed' => 'no']);
        BuilderMacroUser::create(['name' => 'Unknown User', 'verified' => null, 'blocked' => null, 'reviewed' => null]);
    }

    protected function tearDown(): void
    {
        Schema::drop('builder_macro_users');
        parent::tearDown();
    }

    public function test_where_ternary_true_filters_rows(): void
    {
        $users = BuilderMacroUser::query()->whereTernaryTrue('verified')->get();

        $this->assertCount(1, $users);
        $this->assertSame('True User', $users->first()->name);
    }

    public function test_where_any_and_all_helpers(): void
    {
        $any = BuilderMacroUser::query()->whereAnyTernaryTrue(['verified', 'reviewed'])->get();
        $this->assertCount(1, $any);

        $all = BuilderMacroUser::query()->whereAllTernaryTrue(['verified', 'reviewed'])->get();
        $this->assertCount(1, $all);
        $this->assertSame('True User', $all->first()->name);
    }

    public function test_order_and_consensus_helpers(): void
    {
        $ordered = BuilderMacroUser::query()->orderByTernary('verified')->pluck('name')->all();
        $this->assertSame(['True User', 'Unknown User', 'False User'], $ordered);

        $consensus = BuilderMacroUser::query()->ternaryConsensus(['verified', 'reviewed'], 'true');
        $this->assertCount(1, $consensus);
        $this->assertSame('True User', $consensus->first()->name);
    }
}

class BuilderMacroUser extends Model
{
    protected $table = 'builder_macro_users';
}
