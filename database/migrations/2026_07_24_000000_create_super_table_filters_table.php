<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_table_filters', function (Blueprint $table) {
            $table->id();

            // A plain string morph (not ->nullableMorphs()/->nullableUlidMorphs()) so this
            // works whether the consuming app's user model uses auto-increment ids or ULIDs.
            $table->string('userable_type')->nullable();
            $table->string('userable_id')->nullable();
            $table->index(['userable_type', 'userable_id']);

            $table->string('key')->index();
            $table->string('name');
            $table->json('metadata');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_table_filters');
    }
};
