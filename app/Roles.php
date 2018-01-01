<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use App\User;

class Roles extends Model
{
    // returns users of all roles,
    // a set of roles
    // or a single role
    public function getRoleUsers($role = null) { }

    // returns an array of roles assigned to a user
    public function getUserRoles($userId) { }

    public function revokeRole($userId, $role) { }

    public function assignRole($userId, $role) { }
}
