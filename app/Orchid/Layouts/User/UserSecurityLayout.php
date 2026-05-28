<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserSecurityLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('user.status_id')
                ->options($this->query->get('user_status_options', []))
                ->title(tkey('security.users.fields.status'))
                ->empty(tkey('security.users.placeholders.status')),

            Select::make('user.preferred_locale')
                ->options($this->query->get('locale_options', []))
                ->title(tkey('security.users.fields.preferred_locale'))
                ->empty(tkey('security.users.placeholders.preferred_locale')),

            Input::make('user.timezone')
                ->type('text')
                ->max(64)
                ->title(tkey('security.users.fields.timezone'))
                ->placeholder(config('app.timezone', 'Europe/Vilnius')),

            CheckBox::make('user.must_change_password')
                ->sendTrueOrFalse()
                ->title(tkey('security.users.fields.must_change_password')),
        ];
    }
}
