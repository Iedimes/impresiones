<?php

namespace App\Http\Requests;

use Backpack\PermissionManager\app\Http\Requests\UserUpdateCrudRequest;

class AdminUserUpdateRequest extends UserUpdateCrudRequest
{
    public function rules()
    {
        $id = $this->get('id') ?? request()->route('id');
        $userModel = config('backpack.permissionmanager.models.user', 'users');

        return [
            'username' => 'required|string|max:255|unique:' . $userModel . ',username,' . $id,
            'email'    => 'required|email|unique:' . $userModel . ',email,' . $id,
            'name'     => 'required|string|max:255',
            'password' => 'confirmed',
        ];
    }
}

