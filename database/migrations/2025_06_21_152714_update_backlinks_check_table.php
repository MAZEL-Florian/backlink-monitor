<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backlink_checks', function (Blueprint $table) {
            $table->string('source_domain')->nullable()->after('backlink_id');
            $table->text('target_url')->nullable()->after('source_domain');
            $table->integer('domain_authority')->nullable()->after('exact_match');
            $table->integer('page_authority')->nullable()->after('domain_authority');
            $table->text('notes')->nullable()->after('page_authority');
            $table->timestamp('first_found_at')->nullable()->after('notes');
            $table->json('metadata')->nullable()->after('first_found_at');
            $table->string('check_type')->default('automatic')->after('metadata');
            $table->text('raw_response')->nullable()->after('check_type');
        });
    }

    public function down(): void
    {
        Schema::table('backlink_checks', function (Blueprint $table) {
            $table->dropColumn([
                'source_domain',
                'target_url', 
                'domain_authority',
                'page_authority',
                'notes',
                'first_found_at',
                'metadata',
                'check_type',
                'raw_response'
            ]);
        });
    }
};
