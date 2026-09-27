<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * footer_settings is the single site-settings row the storefront already
     * loads on every page. Extend it with the remaining editable copy
     * (contact page, newsletter block, About page) so none of it is hardcoded.
     */
    public function up(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            // Contact page
            $table->text('business_hours')->nullable();
            $table->text('map_embed_url')->nullable();

            // Homepage newsletter block
            $table->string('newsletter_title')->nullable();
            $table->text('newsletter_text')->nullable();

            // About page
            $table->string('about_title')->nullable();
            $table->longText('about_content')->nullable();
            $table->string('about_image')->nullable();
            $table->json('about_values')->nullable()->comment('Array of {title, description}');
            $table->json('about_stats')->nullable()->comment('Array of {value, label}');
            $table->json('about_team')->nullable()->comment('Array of {name, role, photo}');
        });
    }

    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn([
                'business_hours', 'map_embed_url', 'newsletter_title', 'newsletter_text',
                'about_title', 'about_content', 'about_image', 'about_values', 'about_stats', 'about_team',
            ]);
        });
    }
};
