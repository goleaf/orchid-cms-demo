<?php

namespace App\Orchid\Screens\Website;

use App\Actions\UpdateSiteSettingsAction;
use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Models\Branch;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SiteSettingsScreen extends Screen
{
    /**
     * @var array<int, string>
     */
    private array $branches = [];

    public function query(): iterable
    {
        $settings = SiteSetting::query()
            ->select(['key', 'value'])
            ->whereIn('key', $this->settingKeys())
            ->get()
            ->keyBy('key');

        $this->branches = Branch::query()
            ->forAdminList()
            ->ordered()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();

        return [
            'default_phone' => $this->settingValue($settings, 'default_phone', '+370 600 00000'),
            'default_email' => $this->settingValue($settings, 'default_email', 'info@example.test'),
            'default_currency' => $this->settingValue($settings, 'default_currency', 'EUR'),
            'social_links' => $this->encodedSettingValue($settings, 'social_links', ['facebook' => null, 'instagram' => null]),
            'hero_image' => $this->settingValue($settings, 'hero_image'),
            'robots_txt' => $this->settingValue($settings, 'robots_txt'),
            'default_branch_id' => $this->settingValue($settings, 'default_branch_id'),
            'cookie_notice_enabled' => (int) (bool) $this->settingValue($settings, 'cookie_notice_enabled', true),
            'analytics_enabled' => (int) (bool) $this->settingValue($settings, 'analytics_enabled', false),
        ];
    }

    public function name(): ?string
    {
        return tkey('website.admin.settings.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.settings.description');
    }

    public function permission(): iterable
    {
        return ['website.manage_settings'];
    }

    public function commandBar(): iterable
    {
        return [
            Button::make(tkey('website.admin.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('default_phone')
                    ->title(tkey('website.admin.settings.fields.default_phone')),
                Input::make('default_email')
                    ->type('email')
                    ->title(tkey('website.admin.settings.fields.default_email')),
                Input::make('default_currency')
                    ->maxlength(3)
                    ->title(tkey('website.admin.settings.fields.default_currency')),
                Select::make('default_branch_id')
                    ->title(tkey('website.admin.settings.fields.default_branch_id'))
                    ->empty(tkey('website.admin.filters.no_branch'), '')
                    ->options($this->branches),
            ])->title(tkey('website.admin.sections.main')),

            Layout::rows([
                TextArea::make('social_links')
                    ->rows(5)
                    ->title(tkey('website.admin.settings.fields.social_links')),
                Input::make('hero_image')
                    ->title(tkey('website.admin.settings.fields.hero_image')),
                TextArea::make('robots_txt')
                    ->rows(7)
                    ->title(tkey('website.admin.settings.fields.robots_txt')),
            ])->title(tkey('website.admin.sections.content')),

            Layout::rows([
                Select::make('cookie_notice_enabled')
                    ->title(tkey('website.admin.settings.fields.cookie_notice_enabled'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
                Select::make('analytics_enabled')
                    ->title(tkey('website.admin.settings.fields.analytics_enabled'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
            ])->title(tkey('website.admin.sections.system')),
        ];
    }

    public function save(UpdateSiteSettingsRequest $request, UpdateSiteSettingsAction $update): RedirectResponse
    {
        $update->handle($request->settingsData());

        Toast::info(tkey('website.admin.settings.messages.saved'));

        return redirect()->route('platform.website.settings');
    }

    /**
     * @return array<int, string>
     */
    private function settingKeys(): array
    {
        return [
            'default_phone',
            'default_email',
            'default_currency',
            'social_links',
            'hero_image',
            'robots_txt',
            'default_branch_id',
            'cookie_notice_enabled',
            'analytics_enabled',
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<string, SiteSetting>  $settings
     */
    private function settingValue($settings, string $key, mixed $fallback = null): mixed
    {
        return $settings->get($key)?->value ?? $fallback;
    }

    /**
     * @param  \Illuminate\Support\Collection<string, SiteSetting>  $settings
     */
    private function encodedSettingValue($settings, string $key, mixed $fallback = null): string
    {
        $value = $this->settingValue($settings, $key, $fallback);

        return is_array($value)
            ? (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : (string) $value;
    }
}
