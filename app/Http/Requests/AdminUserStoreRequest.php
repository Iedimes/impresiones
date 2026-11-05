<?php

namespace App\Http\Requests;

use Backpack\PermissionManager\app\Http\Requests\UserStoreCrudRequest;

class AdminUserStoreRequest extends UserStoreCrudRequest
{
    public function rules()
    {
        $userModel = config('backpack.permissionmanager.models.user', 'users');

        return [
            'username' => 'required|string|max:255|unique:' . $userModel . ',username',
            'email'    => 'required|email|unique:' . $userModel . ',email',
            'name'     => 'required|string|max:255',
            'password' => 'required|confirmed',
        ];
    }
}

