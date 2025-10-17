<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvestTypeAndMaturityDateToStackingDeposite extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stackingdeposite', function (Blueprint $table) {
            // 1 => Dragging (daily ROI), 2 => Locking (1 year payout)
            $table->tinyInteger('invest_type')
                  ->default(0)
                  ->after('staketype')
                  ->comment('1=Dragging (daily ROI), 2=Locking (1-year maturity)');

            // For Locking deposits: the date when payout is due
            $table->date('maturity_date')
                  ->nullable()
                  ->after('invest_type')
                  ->comment('Maturity date for Locking deposits (1 year from start)');

            // Status of Locking payout: 0=pending, 1=paid
            $table->tinyInteger('maturity_status')
                  ->default(0)
                  ->after('maturity_date')
                  ->comment('0=pending, 1=paid for Locking deposits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stackingdeposite', function (Blueprint $table) {
            $table->dropColumn(['invest_type', 'maturity_date', 'maturity_status']);
        });
    }
}
