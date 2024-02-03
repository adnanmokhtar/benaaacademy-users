<?php

namespace Benaaacademy\Users\Models;

use Benaaacademy\Media\Models\Media;
use Benaaacademy\Platform\Model;
use Benaaacademy\Roles\Models\Role;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/*
 * Class User
 * @package Benaaacademy\Users\Models
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{

    use Authenticatable, Authorizable, CanResetPassword;

    /*
     * @var array
     */
    protected $creatingRules = [
        'username' => 'required|unique:users',
        "email" => "required|email|unique:users",
        "first_name" => "required"
    ];

    /*
     * @var array
     */
    protected $updatingRules = [
        "username" => "required|unique:users,username,[id],id",
        "email" => "required|email|unique:users,email,[id],id",
        "first_name" => "required"
    ];

    /*
     * @var array
     */
    protected $searchable = [
        "username", "email", "first_name"
    ];

    /*
     * @var string
     */
    protected $table = 'users';

    /*
     * @var array
     */
    protected $guarded = ['id', "permission"];

    /*
     * Set create validations
     * @param $v
     * @return mixed
     */
    function setCreateValidation($v)
    {
        $v->sometimes(["password", "repassword"], "required|same:repassword", function ($input) {
            return $input->provider == NULL;
        });

        return $v;
    }


    /*
     * Set update validations
     * @param $v
     * @return mixed
     */
    function setUpdateValidation($v)
    {
        $v->sometimes(["password", "repassword"], "required|same:repassword", function ($input) {
            return $input->provider == NULL and $input->password != "";
        });

        return $v;
    }

    /*
     * Set password attribute
     * @param $password
     */
    function setPasswordAttribute($password)
    {
        if (trim($password) != "") {
            $this->attributes["password"] = Hash::make($password);
        } else {
            unset($this->attributes["password"]);
        }
    }

    /*
     * Set repassword attribute
     * @param $password
     */
    function setRepasswordAttribute($password)
    {
        unset($this->attributes["repassword"]);
    }

    /*
     * Name getter
     * @return mixed|string
     */
    public function getNameAttribute()
    {

        $name = $this->first_name . ' ' . $this->last_name;

        if (trim($name) == "") {
            $name = $this->username;
        }

        return $name;
    }

    /*
     * photo relation
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function photo()
    {
        return $this->hasOne(Media::class, 'id', 'photo_id');
    }

    /*
     * Photo url attribute
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return thumbnail($this->photo->path, "thumbnail", "admin::images/author.png");
        } else {
            return assets("admin::images/author.png");
        }
    }

    /*
     * Role relation
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function role()
    {
        return $this->hasOne(Role::class, "id", 'role_id');
    }

    /*
     * Determine if the entity has a given ability.
     *
     * @param  string $ability
     * @param  array|mixed $arguments
     * @return bool
     */
    public function can($ability, $arguments = [])
    {
        if ($arguments) {
            return app(Gate::class)->forUser($this)->check($ability, $arguments);
        }

        return $this->hasAccess($ability);
    }

    /*
     * Check user has specific permissions
     * @param array $params
     * @return bool
     */
    public function hasAccess($params = array())
    {

        if ($this->hasRole("superadmin")) {
            return true;
        }

        $params = is_array($params) ? $params : func_get_args();

        $user = Auth::guard(GUARD)->user();

        $permissions = [];

        if ($user->role) {
            $permissions = (array)$user->role->permissions->pluck("permission")->toArray();
        }

        if (count($permissions) == 0) {
            return false;
        }

        $permissions_string = join("", $permissions);

        if (count($params)) {
            foreach ($params as $param) {
                if (!in_array($param, $permissions)) {
                    if (!strstr($permissions_string, $param . ".")) {
                        return false;
                    }
                }
            }
            return true;
        }

        return false;
    }

    /*
     * Check user has specific role
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        if($this->role) {
            return strtolower($role) == strtolower($this->role->name);
        }

        return false;
    }

    /*
     * generate API token
     * @return string
     */
    function newApiToken()
    {
        return Str::random(60);
    }

}
