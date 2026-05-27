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
        MarketingLead::query()
            ->where('status', 'qualified')
            ->update(['status' => LeadStatus::ConsultationDone->value]);

        MarketingLead::query()
            ->where('status', 'converted')
            ->update(['status' => LeadStatus::BecameStudent->value]);

        MarketingLead::query()
            ->where('status', 'lost')
            ->update(['status' => LeadStatus::Rejected->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MarketingLead::query()
            ->where('status', LeadStatus::ConsultationDone->value)
            ->update(['status' => 'qualified']);

        MarketingLead::query()
            ->where('status', LeadStatus::BecameStudent->value)
            ->update(['status' => 'converted']);

        MarketingLead::query()
            ->where('status', LeadStatus::Rejected->value)
            ->update(['status' => 'lost']);
    }
};
