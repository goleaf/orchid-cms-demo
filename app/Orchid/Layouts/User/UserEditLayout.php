<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class UserEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('user.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title(tkey('security.users.fields.name'))
                ->placeholder(tkey('security.users.fields.name')),

            Input::make('user.email')
                ->type('email')
                ->required()
                ->title(tkey('security.users.fields.email'))
                ->placeholder(tkey('security.users.fields.email')),
        ];
    }
}
