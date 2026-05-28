<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Actions\Security\DeleteUserAction;
use App\Actions\Security\SaveUserAction;
use App\Http\Requests\Security\UserRequest;
use App\Models\User;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Orchid\Layouts\User\UserFiltersLayout;
use App\Orchid\Layouts\User\UserListLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $relations = Schema::hasTable('user_statuses') ? ['roles', 'status'] : ['roles'];

        return [
            'users' => User::with($relations)
                ->filters(UserFiltersLayout::class)
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return tkey('security.users.title');
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
            Link::make(tkey('security.users.actions.add'))
                ->icon('bs.plus-circle')
                ->route('platform.systems.users.create'),
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
            UserFiltersLayout::class,
            UserListLayout::class,

            Layout::modal('editUserModal', UserEditLayout::class)
                ->deferred('loadUserOnOpenModal'),
        ];
    }

    /**
     * Loads user data when opening the modal window.
     *
     * @return array
     */
    public function loadUserOnOpenModal(User $user): iterable
    {
        return [
            'user' => $user,
        ];
    }

    public function saveUser(UserRequest $request, User $user, SaveUserAction $saveUser): void
    {
        $saveUser->handle($user, $request, $request->user());

        Toast::info(tkey('security.users.messages.saved'));
    }

    public function remove(Request $request, DeleteUserAction $deleteUser): void
    {
        $deleteUser->handle(User::findOrFail($request->get('id')), $request->user(), $request);

        Toast::info(tkey('security.users.messages.removed'));
    }
}
