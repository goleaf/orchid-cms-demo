<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Role;

use App\Actions\Security\DeleteRoleAction;
use App\Actions\Security\SaveRoleAction;
use App\Http\Requests\Security\RoleRequest;
use App\Orchid\Layouts\Role\RoleEditLayout;
use App\Orchid\Layouts\Role\RolePermissionLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Platform\Models\Role;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class RoleEditScreen extends Screen
{
    /**
     * @var Role
     */
    public $role;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Role $role): iterable
    {
        return [
            'role' => $role,
            'permission' => $role->statusOfPermissions(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->role->exists ? tkey('security.roles.edit_title') : tkey('security.roles.create_title');
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return tkey('security.roles.description');
    }

    /**
     * The permissions required to access this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.roles',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(tkey('security.roles.actions.save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make(tkey('security.roles.actions.remove'))
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->role->exists),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                RoleEditLayout::class,
            ])
                ->title(tkey('security.roles.blocks.role.title'))
                ->description(tkey('security.roles.blocks.role.description')),

            Layout::block([
                RolePermissionLayout::class,
            ])
                ->title(tkey('security.roles.blocks.permissions.title'))
                ->description(tkey('security.roles.blocks.permissions.description')),
        ];
    }

    /**
     * @return RedirectResponse
     */
    public function save(RoleRequest $request, Role $role, SaveRoleAction $saveRole): RedirectResponse
    {
        $saveRole->handle($role, $request, $request->user());

        Toast::info(tkey('security.roles.messages.saved'));

        return redirect()->route('platform.systems.roles');
    }

    /**
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function remove(Role $role, Request $request, DeleteRoleAction $deleteRole): RedirectResponse
    {
        $deleteRole->handle($role, $request->user(), $request);

        Toast::info(tkey('security.roles.messages.removed'));

        return redirect()->route('platform.systems.roles');
    }
}
