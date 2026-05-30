<?php

namespace App\Http\Requests\Security\Concerns;

use App\Models\User;

trait ResolvesTargetUser
{
    protected function targetUser(): ?User
    {
        $routeUser = $this->route('user');

        if ($routeUser instanceof User) {
            return $routeUser;
        }

        $id = $this->input('user_id') ?: $this->input('user.id') ?: $this->route('id');

        return filled($id) ? User::query()->find($id) : null;
    }
}
