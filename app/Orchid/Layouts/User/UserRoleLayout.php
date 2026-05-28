<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\Branch;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserRoleLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Select::make('user.roles.')
                ->fromModel(Role::class, 'name')
                ->multiple()
                ->title(tkey('security.users.fields.roles'))
                ->help(tkey('security.users.help.roles')),

            Select::make('user.branch_ids.')
                ->fromModel(Branch::class, 'name')
                ->multiple()
                ->title(tkey('security.users.fields.branches'))
                ->help(tkey('security.users.help.branches')),

            CheckBox::make('user.is_active')
                ->sendTrueOrFalse()
                ->title(tkey('security.users.fields.is_active')),

            Input::make('user.security_lock_reason')
                ->type('text')
                ->max(255)
                ->title(tkey('security.users.fields.security_lock_reason')),
        ];
    }
}
