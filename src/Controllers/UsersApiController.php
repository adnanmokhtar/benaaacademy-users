<?php

namespace Benaaacademy\Users\Controllers;

use Benaaacademy\Platform\APIController;
use Benaaacademy\Users\Models\User;
use Illuminate\Http\Request;

/*
 * Class UsersApiController
 */
class UsersApiController extends APIController
{

    /*
     * UsersApiController constructor.
     */
    function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware("permission:users.manage");
    }


    /*
     * List users
     * @param int $id (optional) The object identifier.
     * @param string $q (optional) The search query string.
     * @param array $with (optional) extra related user components [photo].
     * @param int $limit (default: 10) The number of retrieved records.
     * @param int $page (default: 1) The page number.
     * @param string $order_by (default: id) The column you wish to sort by.
     * @param string $order_direction (default: DESC) The sort direction ASC or DESC.
     * @return \Illuminate\Http\JsonResponse
     */
    function show(Request $request)
    {

        $id = $request->get("id");
        $limit = $request->get("limit", 10);
        $sort_by = $request->get("order_by", "id");
        $sort_direction = $request->get("order_direction", "DESC");

        $components = array_filter($request->get("with", []));

        foreach ($components as $relation => $data) {
            $components[$relation] = function ($query) use ($data) {
                return $query->orderBy(array_get($data, 'order_by', "id"), array_get($data, 'order_direction', "DESC"));
            };
        }

        $query = User::with($components)->orderBy($sort_by, $sort_direction);

        if ($request->filled("q")) {
            $query->search($request->get("q"));
        }

        if ($id) {
            $users = $query->where("id", $id)->first();
        } else {
            $users = $query->paginate($limit)->appends($request->all());
        }

        return $this->response($users);

    }


    /*
     * Create a new user
     * @param string $username (required) The user name.
     * @param string $password (required) The user password.
     * @param string $email (required) The user email.
     * @param string $first_name (required) The user first name.
     * @param string $last_name (optional) The user last name.
     * @param string $provider (optional) The Auth provider.
     * @param string $provider_id (optional) The Auth provider id.
     * @param int $role_id (default:0) The user role id.
     * @param int $photo_id (default:0) The user photo id.
     * @param int $photo_data (optional) The user base64 photo data.
     * @param int $photo_url (optional) The user photo external url.
     * @param bool $status (default:0) The user status.
     * @param bool $backend (default:0) The user backend access status.
     * @param string $lang (default:'en') The user default lang.
     * @param string $color (default:'blue') The user backend color theme.
     * @param string $about (optional) The user bio.
     * @param string $facebook (optional) The user facebook page.
     * @param string $twitter (optional) The user twitter page.
     * @param string $linked_in (optional) The user linked_in page.
     * @param string $google_plus (optional) The user google+ page.
     * @return \Illuminate\Http\JsonResponse
     */
    function create(Request $request)
    {

        /*
            check if social user
        */

        if ($request->filled("provider") and $request->filled("provider_id")) {

            $user = User::with("photo")
                ->where("provider", $request->get("provider"))
                ->where("provider_id", $request->get("provider_id"))
                ->first();

            if (count($user)) {
                return $this->response($user);
            }

        }

        $user = new User();

        $user->username = $request->username;
        $user->password = $request->password;
        $user->repassword = $request->password;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->provider = $request->provider;
        $user->provider_id = $request->provider_id;
        $user->status = $request->get("status", 1);
        $user->backend = $request->get("backend", 0);
        $user->lang = $request->get("lang", "ar");
        $user->color = $request->get("color", "blue");
        $user->about = $request->about;
        $user->facebook = $request->facebook;
        $user->twitter = $request->twitter;
        $user->linked_in = $request->linked_in;
        $user->google_plus = $request->google_plus;
        $user->role_id = $request->get("role_id", 0);
        $user->photo_id = $request->get("photo_id", 0);
        $user->api_token = $user->newApiToken();

        if ($request->filled("photo_data")) {
            $media = new Media();
            $media = $media->saveContent($request->get("photo_data"), NULL, "api");
            $user->photo_id = $media->id;
        }

        if ($request->filled("photo_url")) {
            $media = new Media();
            $media = $media->saveLink($request->get("photo_url"), "api");
            $user->photo_id = $media->id;
        }

        // Validate and save requested user
        if (!$user->validate()) {

            // Exception for repassword field
            $validation_errors = $user->errors()->toArray();
            if (isset($validation_errors["repassword"])) {
                unset($validation_errors["repassword"]);
            }
            // return validation error
            return $this->response($validation_errors, "validation error");

        }

        if ($user->save()) {
            $user->load("photo");
            return $this->response($user);
        }


    }

    /*
     * Update user by id
     * @param int $id (required) The user id.
     * @param string $username (optional) The user name.
     * @param string $password (optional) The user password.
     * @param string $email (optional) The user email.
     * @param string $first_name (optional) The user first name.
     * @param string $last_name (optional) The user last name.
     * @param int $role_id (default:0) The user role id.
     * @param int $photo_id (default:0) The user photo id.
     * @param int $photo_data (optional) The user base64 photo data.
     * @param int $photo_url (optional) The user photo external url.
     * @param bool $status (default:0) The user status.
     * @param bool $backend (default:0) The user backend access status.
     * @param string $lang (default:'en') The user default lang.
     * @param string $color (default:'blue') The user backend color theme.
     * @param string $about (optional) The user bio.
     * @param string $facebook (optional) The user facebook page.
     * @param string $twitter (optional) The user twitter page.
     * @param string $linked_in (optional) The user linked_in page.
     * @param string $google_plus (optional) The user google+ page.
     * @return \Illuminate\Http\JsonResponse
     */
    function update(Request $request)
    {

        if (!$request->id) {
            return $this->error("Missing user id");
        }

        $user = User::find($request->id);

        if (!$user) {
            return $this->error("User #" . $request->id . " is not exists");
        }

        $user->username = $request->get("username", $user->username);

        if ($request->get("password")) {
            $user->password = $request->get("password");
            $user->repassword = $user->password;
        }

        $user->first_name = $request->get("first_name", $user->first_name);
        $user->last_name = $request->get("last_name", $user->last_name);
        $user->email = $request->get("email", $user->email);
        $user->provider = $request->get("provider", $request->provider);
        $user->provider_id = $request->get("provider_id", $request->provider_id);
        $user->backend = $request->get("backend", $user->backend);
        $user->status = $request->get("status", $user->status);
        $user->lang = $request->get("lang", $user->lang);
        $user->role_id = $request->get("role_id", $user->role_id);
        $user->color = $request->get("color", $user->color);
        $user->about = $request->get("about", $user->about);
        $user->facebook = $request->get("facebook", $user->facebook);
        $user->twitter = $request->get("twitter", $user->twitter);
        $user->linked_in = $request->get("linked_in", $user->linked_in);
        $user->google_plus = $request->get("google_plus", $user->google_plus);
        $user->photo_id = $request->get("photo_id", $user->photo_id);

        if ($request->filled("photo_data")) {
            $media = new Media();
            $media = $media->saveContent($request->get("photo_data"), NULL, "api");
            $user->photo_id = $media->id;
        }

        if ($request->filled("photo_url")) {
            $media = new Media();
            $media = $media->saveLink($request->get("photo_url"), "api");
            $user->photo_id = $media->id;
        }

        // Validate and save requested user
        if (!$user->validate()) {

            // Exception for repassword field
            $validation_errors = $user->errors()->toArray();
            if (isset($validation_errors["repassword"])) {
                unset($validation_errors["repassword"]);
            }
            // return validation error
            return $this->response($validation_errors, "validation error");

        }

        if ($user->save()) {
            $user->load("photo");
            return $this->response($user);
        }


    }

    /*
     * Delete user by id
     * @param int $id (required) The user id.
     * @return \Illuminate\Http\JsonResponse
     */
    function destroy(Request $request)
    {

        if (!$request->id) {
            return $this->error("Missing user id");
        }

        $user = User::find($request->id);

        if (!$user) {
            return $this->error("User #" . $request->id . " is not exists");
        }

        // Destroy requested user
        $user->delete();

        return $this->response($user);

    }


}
