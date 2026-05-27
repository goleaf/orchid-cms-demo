<?php

namespace App\Actions;

use App\Models\SiteSetting;

class GenerateRobotsTxtAction
{
    public function handle(): string
    {
        $override = SiteSetting::query()
            ->where('key', 'robots_txt')
            ->first(['value'])
            ?->value;

        if (is_string($override) && filled($override)) {
            return $this->normalize($override);
        }

        return $this->normalize(implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /platform',
            'Disallow: /platform/',
            'Sitemap: '.route('site.sitemap'),
        ]));
    }

    private function normalize(string $content): string
    {
        return rtrim($content)."\n";
    }
}
