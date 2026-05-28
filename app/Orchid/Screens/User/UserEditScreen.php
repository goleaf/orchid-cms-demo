<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Actions\Security\DeleteUserAction;
use App\Actions\Security\SaveUserAction;
use App\Http\Requests\Security\UserRequest;
use App\Models\User;
use App\Orchid\Layouts\Role\RolePermissionLayout;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Orchid\Layouts\User\UserPasswordLayout;
use App\Orchid\Layouts\User\UserRoleLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Access\Impersonation;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserEditScreen extends Screen
{
    /**
     * @var User
     */
    public $user;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(User $user): iterable
    {
        $user->load(['roles', 'accessibleBranches']);

        if (! $user->exists) {
            $user->is_active = true;
        }

        return [
            'user' => $user,
            'user.branch_ids' => $user->accessibleBranches->pluck('id')->all(),
            'permission' => $user->statusOfPermissions(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->user->exists ? tkey('security.users.edit_title') : tkey('security.users.create_title');
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return tkey('security.users.description');
    }

    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
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
            Button::make(tkey('security.users.actions.impersonate'))
                ->icon('bg.box-arrow-in-right')
                ->confirm(tkey('security.users.confirm_impersonate'))
                ->method('loginAs')
                ->canSee($this->user->exists && $this->user->id !== request()->user()->id && ! $this->user->isLockedOut()),

            Button::make(tkey('security.users.actions.remove'))
                ->icon('bs.trash3')
                ->confirm(tkey('security.users.confirm_delete'))
                ->method('remove')
                ->canSee($this->user->exists),

            Button::make(tkey('security.users.actions.save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::block(UserEditLayout::class)
                ->title(tkey('security.users.blocks.profile.title'))
                ->description(tkey('security.users.blocks.profile.description'))
                ->commands(
                    Button::make(tkey('common.actions.save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserPasswordLayout::class)
                ->title(tkey('security.users.blocks.password.title'))
                ->description(tkey('security.users.blocks.password.description'))
                ->commands(
                    Button::make(tkey('common.actions.save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserRoleLayout::class)
                ->title(tkey('security.users.blocks.roles.title'))
                ->description(tkey('security.users.blocks.roles.description'))
                ->commands(
                    Button::make(tkey('common.actions.save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(RolePermissionLayout::class)
                ->title(tkey('security.users.blocks.permissions.title'))
                ->description(tkey('security.users.blocks.permissions.description'))
                ->commands(
                    Button::make(tkey('common.actions.save'))
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

        ];
    }

    /**
     * @return RedirectResponse
     */
    public function save(User $user, UserRequest $request, SaveUserAction $saveUser): RedirectResponse
    {
        $saveUser->handle($user, $request, $request->user());

        Toast::info(tkey('security.users.messages.saved'));

        return redirect()->route('platform.systems.users');
    }

    /**
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function remove(User $user, Request $request, DeleteUserAction $deleteUser): RedirectResponse
    {
        $deleteUser->handle($user, $request->user(), $request);

        Toast::info(tkey('security.users.messages.removed'));

        return redirect()->route('platform.systems.users');
    }

    /**
     * @return RedirectResponse
     */
    public function loginAs(User $user)
    {
        Impersonation::loginAs($user);

        Toast::info(tkey('security.users.messages.impersonating'));

        return redirect()->route(config('platform.index'));
    }
}
