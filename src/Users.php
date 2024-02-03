<?php

namespace Benaaacademy\Users;

use Action;
use Benaaacademy\Users\Models\User;
use Illuminate\Support\Facades\Auth;
use Navigation;

class Users extends \Benaaacademy\Platform\Plugin
{

    protected $dependencies = [
        "roles" => \Benaaacademy\Roles\Roles::class,
    ];

    protected $permissions = [
        "show",
        "create",
        "edit",
        "delete",
    ];

    function boot()
    {

        parent::boot();

        $this->registerPolices();

        Navigation::menu("sidebar", function ($menu) {

            if (Auth::user()->can('users')) {
                $menu->item('users', trans("admin::common.users"), route("admin.users.show"))
                    ->order(16)
                    ->icon("fa-users");
            }

        });

        Action::listen("dashboard.featured", function () {
            if (Auth::user()->can('users')) {
                $users = User::orderBy("created_at", "DESC")->limit(5)->get();
                return view("users::widgets.users", ["users" => $users]);
            }
        });

    }


    function registerPolices()
    {

        /*
         * Users allowed to edit:
         * Super admins are allowed to edit all users
         * Users given permission to edit are allowed to edit all other users
         * All users allowed to edit their profile
         */
        $this->gate->define("users.edit", function ($user, $profile) {
            return $user->hasRole("superadmin")
                || $user->hasAccess("users.edit")
                || $user->id == $profile->id;

        });

        /*
         * Users allowed to delete:
         * Super admins are allowed to delete all users
         * Users given permission to delete are allowed to delete all users
         * Users are not allowed to delete themselves
         */
        $this->gate->define("users.delete", function ($user, $profile = false) {

            if ($profile) {
                return $user->hasAccess("users.delete") and $user->id != $profile->id;
            } else {
                return $user->hasAccess("users.delete");
            }

        });
    }
}
