<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Models\User;
use App\Orchid\Layouts\User\ProfilePasswordLayout;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Services\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Access\Impersonation;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserProfileScreen extends Screen
{
    /**
     * @var array<string, string>
     */
    private array $localeOptions = [];

    private string $currentLocale = '';

    /**
     * Fetch data to be displayed on the screen.
     *
     *
     * @return array
     */
    public function query(Request $request, LocaleManager $locales): iterable
    {
        $this->localeOptions = $locales->languageOptions();
        $this->currentLocale = $request->user()?->preferred_locale ?: $locales->resolve($request);

        return [
            'user' => $request->user(),
            'locale' => $this->currentLocale,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return tkey('profile.title');
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return tkey('profile.description');
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(tkey('profile.actions.back_to_account'))
                ->novalidate()
                ->canSee(Impersonation::isSwitch())
                ->icon('bs.people')
                ->route('platform.switch.logout'),

            Button::make(tkey('profile.actions.sign_out'))
                ->novalidate()
                ->icon('bs.box-arrow-left')
                ->route('platform.logout'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(UserEditLayout::class)
                ->title(tkey('profile.blocks.information.title'))
                ->description(tkey('profile.blocks.information.description'))
                ->commands(
                    Button::make(tkey('common.actions.save'))
                        ->type(Color::BASIC())
                        ->icon('bs.check-circle')
                        ->method('save')
                ),

            Layout::block(Layout::rows([
                Select::make('locale')
                    ->title(tkey('locale.fields.preferred_locale'))
                    ->options($this->localeOptions)
                    ->value($this->currentLocale)
                    ->required(),
            ]))
                ->title(tkey('locale.profile_title'))
                ->description(tkey('locale.profile_description'))
                ->commands(
                    Button::make(tkey('common.actions.save'))
                        ->type(Color::BASIC())
                        ->icon('bs.check-circle')
                        ->method('saveLocale')
                ),

            Layout::block(ProfilePasswordLayout::class)
                ->title(tkey('profile.blocks.password.title'))
                ->description(tkey('profile.blocks.password.description'))
                ->commands(
                    Button::make(tkey('profile.actions.update_password'))
                        ->type(Color::BASIC())
                        ->icon('bs.check-circle')
                        ->method('changePassword')
                ),
        ];
    }

    public function save(Request $request): void
    {
        $request->validate([
            'user.name' => 'required|string',
            'user.email' => [
                'required',
                Rule::unique(User::class, 'email')->ignore($request->user()),
            ],
        ]);

        $request->user()
            ->fill($request->get('user'))
            ->save();

        Toast::info(tkey('profile.messages.updated'));
    }

    public function saveLocale(Request $request, LocaleManager $locales): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string'],
        ]);

        if (! $locales->switch($request, (string) $data['locale'])) {
            Toast::error(tkey('locale.messages.unavailable'));

            return redirect()
                ->back()
                ->withErrors(['locale' => tkey('locale.messages.unavailable')]);
        }

        Toast::info(tkey('locale.messages.saved'));

        return redirect()->back();
    }

    public function changePassword(Request $request): void
    {
        $guard = config('platform.guard', 'web');
        $request->validate([
            'old_password' => 'required|current_password:'.$guard,
            'password' => 'required|confirmed|different:old_password',
        ]);

        tap($request->user(), function ($user) use ($request) {
            $user->password = Hash::make($request->get('password'));
        })->save();

        Toast::info(tkey('profile.messages.password_changed'));
    }
}
