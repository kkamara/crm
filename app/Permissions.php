<?php

namespace App;

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use App\User;

class Permissions extends Model
{
    // returns users of all permissions,
    // a set of permissions
    // or a single permission
    public function getPermissionUsers($permission = null) { }

    // returns an array of permissions assigned to a user
    public function getUserPermissions($userId) { }

    public function revokePermission($userId, $permission) { }

    public function assignPermission($userId, $permission) { }
}

