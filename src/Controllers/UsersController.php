<?php

namespace Benaaacademy\Users\Controllers;

use Action;
use Auth;
use Config;
use Benaaacademy;
use Benaaacademy\Platform\Controller;
use Benaaacademy\Roles\Models\Role;
use Benaaacademy\Users\Models\User;
use Gate;
use Redirect;
use Request;
use Session;
use View;


/*
 * Class UsersController
 * @package Benaaacademy\Users\Controllers
 */

class UsersController extends Controller
{

    /*
     * View payload
     * @var array
     */
    public $data = [];


    /*
     * Show all users
     * @return mixed
     */
    public function index()
    {

        if (Request::isMethod("post")) {
            if (Request::filled("action")) {
                switch (Request::get("action")) {
                    case "delete":
                        return $this->delete();
                }
            }
        }

        $this->data["sort"] = (Request::filled("sort")) ? Request::get("sort") : "created_at";
        $this->data["order"] = (Request::filled("order")) ? Request::get("order") : "desc";
        $this->data['per_page'] = (Request::filled("per_page")) ? Request::get("per_page") : 20;

        $query = User::with("role", "photo")->orderBy($this->data["sort"], $this->data["order"]);

        if (Request::filled("q")) {
            $q = urldecode(Request::get("q"));
            $query->search($q);
        }

        if (Request::filled("per_page")) {
            $this->data["per_page"] = $per_page = Request::get("per_page");
        } else {
            $this->data["per_page"] = $per_page = 20;
        }

        if (Request::filled("backend") and Request::get("backend") == 1) {
            $query->where("role_id", "!=", 0);
        }

        if (Request::filled("status")) {
            $query->where("status", Request::get("status"));
        }

        if (Request::filled("role_id")) {
            $query->where("role_id", Request::get("role_id"));
        }

        $this->data["users"] = $users = $query->paginate($per_page);
        $this->data["roles"] = Role::all();

        return View::make("users::show", $this->data);
    }

    /*
     * Delete user by id
     * @return mixed
     */
    public function delete()
    {

        $ids = Request::get("id");

        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $ID) {

            $user = User::findOrFail($ID);

            if (Auth::user()->can("users.delete", $user)) {

                // Fire deleting action

                Action::fire("user.deleting", $user);

                $user->delete();

                // Fire deleted action

                Action::fire("user.deleted", $user);
            }
        }

        return Redirect::back()->with("message", trans("users::users.events.deleted"));
    }

    /*
     * Create a new user
     * @return mixed
     */
    public function create()
    {

        if (Request::isMethod("post")) {

            $user = new User();

            $user->username = Request::get("username");
            $user->password = Request::get("password");
            $user->repassword = Request::get("repassword");
            $user->email = Request::get("email");
            $user->first_name = Request::get("first_name");
            $user->last_name = Request::get("last_name");
            $user->about = Request::get("about");
            $user->role_id = Request::get("role_id", 0);
            $user->photo_id = Request::get("photo_id", 0);
            $user->lang = Request::get("lang");
            $user->color = Request::get("color", "blue");
            $user->status = Request::get("status", 0);
            $user->facebook = Request::get("facebook");
            $user->twitter = Request::get("twitter");
            $user->linked_in = Request::get("linked_in");
            $user->google_plus = Request::get("google_plus");
            $user->backend = 1;

            // Fire saving action

            Action::fire("user.saving", $user);

            if (!$user->validate()) {
                return Redirect::back()->withErrors($user->errors())->withInput(Request::all());
            }

            $user->save();

            // Fire saved action

            Action::fire("user.saved", $user);

            return Redirect::route("admin.users.edit", array("id" => $user->id))
                ->with("message", trans("users::users.events.created"));

        }

        $this->data["user"] = false;
        $this->data["roles"] = Role::all();

        return View::make("users::edit", $this->data);
    }

    /*
     * Edit user by id
     * @param $user_id
     * @return mixed
     */
    public function edit($user_id)
    {

        $user = User::with("photo")->where("id", $user_id)->first();

        if (!$user) {
            abort(404);
        }

        if (!Auth::user()->can("users.edit", $user)) {
            abort(403);
        }

        if (Request::isMethod("post")) {

            $user->username = Request::get("username");
            $user->password = Request::get("password");
            $user->repassword = Request::get("repassword");
            $user->email = Request::get("email");
            $user->first_name = Request::get("first_name");
            $user->last_name = Request::get("last_name");
            $user->about = Request::get("about");
            $user->role_id = Request::get("role_id", 0);
            $user->photo_id = Request::get("photo_id", 0);
            $user->lang = Request::get("lang");
            $user->color = Request::get("color", "blue");
            $user->status = Request::get("status", 0);
            $user->facebook = Request::get("facebook");
            $user->twitter = Request::get("twitter");
            $user->linked_in = Request::get("linked_in");
            $user->google_plus = Request::get("google_plus");

            // Fire saving action

            Action::fire("user.saving", $user);

            if (!$user->validate()) {
                return Redirect::back()->withErrors($user->errors())->withInput(Request::all());
            }

            $user->save();

            if ($user->id == Auth::user()->id) {
                Session::put('locale', $user->lang);
            }

            // Fire saved action

            Action::fire("user.saved", $user);

            return Redirect::route("admin.users.edit", array("id" => $user->id))
                ->with("message", trans("users::users.events.updated"));

        }

        $this->data["user"] = $user;
        $this->data["roles"] = Role::all();

        return View::make("users::edit", $this->data);
    }

}
