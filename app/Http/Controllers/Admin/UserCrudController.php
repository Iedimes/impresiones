<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminUserStoreRequest;
use App\Http\Requests\AdminUserUpdateRequest;
use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as BaseUserCrudController;

class UserCrudController extends BaseUserCrudController
{
    public function setupListOperation()
    {
        $this->crud->addColumn([
            'name'  => 'username',
            'label' => 'Usuario',
            'type'  => 'text',
        ]);

        parent::setupListOperation();
    }

    public function setupCreateOperation()
    {
        $this->addUserFields();
        $this->crud->setValidation(AdminUserStoreRequest::class);
    }

    public function setupUpdateOperation()
    {
        $this->addUserFields();
        $this->crud->setValidation(AdminUserUpdateRequest::class);
    }

    protected function addUserFields()
    {
        $this->crud->addField([
            'name'  => 'username',
            'label' => 'Usuario (LDAP / Login)',
            'type'  => 'text',
        ]);

        parent::addUserFields();
    }
}

