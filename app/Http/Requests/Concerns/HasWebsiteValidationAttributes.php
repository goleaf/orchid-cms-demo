<?php

namespace App\Http\Requests\Concerns;

trait HasWebsiteValidationAttributes
{
    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => tkey('validation.attributes.website_lead.full_name'),
            'first_name' => tkey('validation.attributes.website_lead.first_name'),
            'last_name' => tkey('validation.attributes.website_lead.last_name'),
            'phone' => tkey('validation.attributes.website_lead.phone'),
            'email' => tkey('validation.attributes.website_lead.email'),
            'course_id' => tkey('validation.attributes.website_lead.course_id'),
            'training_program_id' => tkey('validation.attributes.website_lead.course_id'),
            'branch_id' => tkey('validation.attributes.website_lead.branch_id'),
            'training_group_id' => tkey('validation.attributes.website_lead.training_group_id'),
            'privacy_consent' => tkey('validation.attributes.website_lead.consent_accepted'),
            'consent_accepted' => tkey('validation.attributes.website_lead.consent_accepted'),
            'preferred_time' => tkey('validation.attributes.website_lead.preferred_time'),
            'messenger' => tkey('validation.attributes.website_lead.preferred_messenger'),
            'preferred_messenger' => tkey('validation.attributes.website_lead.preferred_messenger'),
            'message' => tkey('validation.attributes.website_lead.comment'),
            'comment' => tkey('validation.attributes.website_lead.comment'),
            'slug' => tkey('validation.attributes.site_page.slug'),
            'title_translations' => tkey('validation.attributes.site_page.title_translations'),
            'name_translations' => tkey('validation.attributes.course.name_translations'),
            'price' => tkey('validation.attributes.course.price'),
            'old_price' => tkey('validation.attributes.course.price'),
            'program.slug' => tkey('validation.attributes.course.slug'),
            'program.price_eur' => tkey('validation.attributes.course.price'),
            'program.old_price_eur' => tkey('validation.attributes.course.price'),
            'package.slug' => tkey('website.seo.fields.slug'),
            'package.price' => tkey('website.pricing.fields.price'),
            'package.old_price' => tkey('website.pricing.fields.price'),
            'branch.slug' => tkey('validation.attributes.branch.slug'),
            'branch.name_translations' => tkey('validation.attributes.branch.name_translations'),
            'branch.address_translations' => tkey('validation.attributes.branch.address_translations'),
            'page.slug' => tkey('validation.attributes.site_page.slug'),
            'page.title_translations' => tkey('validation.attributes.site_page.title_translations'),
        ];
    }
}
