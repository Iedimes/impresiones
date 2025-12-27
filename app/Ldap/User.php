<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class User extends LdapUser
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    /**
     * The GUID key of the LDAP model.
     *
     * @var string
     */
    protected $guidKey = 'objectguid';
}
