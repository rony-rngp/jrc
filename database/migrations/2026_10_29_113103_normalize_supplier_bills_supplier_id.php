<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parties = DB::table('party')->get();
        foreach ($parties as $p) {
            $name = trim($p->party_name);
            DB::table('supplier_bills')
                ->where('supplier_id', $name)
                ->orWhere('supplier_id', $p->party_name)
                ->update(['supplier_id' => (string)$p->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed as supplier_id should always be numeric party id
    }
};
