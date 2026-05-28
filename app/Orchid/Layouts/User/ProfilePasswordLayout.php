<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Layouts\Rows;

class ProfilePasswordLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Password::make('old_password')
                ->placeholder(tkey('security.profile.placeholders.current_password'))
                ->title(tkey('security.profile.fields.current_password'))
                ->help(tkey('security.profile.help.current_password')),

            Password::make('password')
                ->placeholder(tkey('security.profile.placeholders.new_password'))
                ->title(tkey('security.profile.fields.new_password')),

            Password::make('password_confirmation')
                ->placeholder(tkey('security.profile.placeholders.new_password'))
                ->title(tkey('security.profile.fields.confirm_password'))
                ->help(tkey('security.profile.help.password_policy')),
        ];
    }
}
