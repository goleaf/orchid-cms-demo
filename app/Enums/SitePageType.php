<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum SitePageType: string
{
    use HasEnumValues;

    case Home = 'home';
    case Pricing = 'pricing';
    case Contacts = 'contacts';
    case ThankYou = 'thank_you';
    case PrivacyPolicy = 'privacy_policy';
    case Terms = 'terms';
    case Custom = 'custom';

    public function label(): string
    {
        return tkey('website.admin.pages.types.'.$this->value);
    }
}
