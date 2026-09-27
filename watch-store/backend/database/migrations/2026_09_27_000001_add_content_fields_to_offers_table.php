<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original offers table was created with only id/timestamps. Give it
     * the fields the homepage promo banner needs so the banner is managed
     * from the admin panel instead of being hardcoded in the storefront.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('label')->nullable()->after('id');
            $table->string('title')->default('')->after('label');
            $table->text('description')->nullable()->after('title');
            $table->string('button_text')->nullable()->after('description');
            $table->string('button_url')->nullable()->after('button_text');
            $table->string('image')->nullable()->after('button_url');
            $table->timestamp('starts_at')->nullable()->after('image');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->boolean('is_active')->default(true)->after('ends_at');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'label', 'title', 'description', 'button_text', 'button_url',
                'image', 'starts_at', 'ends_at', 'is_active', 'sort_order',
            ]);
        });
    }
};
