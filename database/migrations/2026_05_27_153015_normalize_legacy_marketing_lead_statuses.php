<?php

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MarketingLead::withoutGlobalScopes()
            ->where('status', 'qualified')
            ->update(['status' => LeadStatus::ConsultationDone->value]);

        MarketingLead::withoutGlobalScopes()
            ->where('status', 'converted')
            ->update(['status' => LeadStatus::BecameStudent->value]);

        MarketingLead::withoutGlobalScopes()
            ->where('status', 'lost')
            ->update(['status' => LeadStatus::Rejected->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MarketingLead::withoutGlobalScopes()
            ->where('status', LeadStatus::ConsultationDone->value)
            ->update(['status' => 'qualified']);

        MarketingLead::withoutGlobalScopes()
            ->where('status', LeadStatus::BecameStudent->value)
            ->update(['status' => 'converted']);

        MarketingLead::withoutGlobalScopes()
            ->where('status', LeadStatus::Rejected->value)
            ->update(['status' => 'lost']);
    }
};
