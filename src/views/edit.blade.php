@extends("admin::layouts.master")

@section("content")

    <form method="post">

        <div class="row wrapper border-bottom white-bg page-heading">

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">

                <h2>
                    <i class="fa fa-users"></i>
                    {{ trans("users::users.edit") }}
                </h2>

                <ol class="breadcrumb">
                    <li>
                        <a href="{{ route("admin") }}">{{ trans("admin::common.admin") }}</a>
                    </li>
                    <li>
                        <a href="{{ route("admin.users.show") }}">{{ trans("users::users.users") }}</a>
                    </li>
                    <li class="active">
                        <strong>{{ trans("users::users.edit") }}</strong>
                    </li>
                </ol>

            </div>

            <div class="col-lg-8 col-md-6 col-sm-6 col-xs-12 text-right">

                @can("users.create")

                    <a href="{{ route("admin.users.create") }}" class="btn btn-primary btn-labeled btn-main">
                        <span class="btn-label icon fa fa-plus"></span> {{trans("users::users.add_new") }}
                    </a>

                @endcan

                <button type="submit" class="btn btn-flat btn-danger btn-main">
                    <i class="fa fa-download" aria-hidden="true"></i>
                    {{ trans("users::users.save_user") }}
                </button>

            </div>
        </div>

        <div class="wrapper wrapper-content fadeInRight">

            @include("admin::partials.messages")

            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">

                        <div class="panel-body">

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input name="username" value="{{ @Request::old("username", $user->username) }}"
                                       class="form-control input-lg"
                                       placeholder="{{ trans("users::users.username") }}"/>
                            </div>

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input name="password" autocomplete="off" value="" class="form-control input-lg"
                                       placeholder="{{ trans("users::users.password") }}" type="password"/>
                            </div>

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input name="repassword" autocomplete="off" value="" class="form-control input-lg"
                                       placeholder="{{ trans("users::users.confirm_password") }}"
                                       type="password"/>
                            </div>

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input name="email" value="{{ @Request::old("email", $user->email) }}"
                                       class="form-control input-lg"
                                       placeholder="{{ trans("users::users.email") }}"
                                       type="email"/>
                            </div>

                            <div class="row">
                                <div class="col-lg-3 col-md-3 text-center">

                                    <div class="row">

                                        <input type="hidden"
                                               value="{{ ($user and $user->photo) ? $user->photo->id : 0 }}"
                                               id="user_photo_id" name="photo_id"/>

                                        <img class="col-lg-12" id="user_photo" style="width: 100%"
                                             src="{{ ($user and $user->photo) ? thumbnail($user->photo->path) : assets("admin::images/user.png") }}"/>


                                        <a href="javascript:void(0)"

                                           @if($user and $user->photo) style="display: none" @endif

                                           id="change_photo"
                                           class="col-lg-12 image-label">{{ trans("users::users.change") }}</a>

                                        <a href="javascript:void(0)"
                                           @if(!$user or ($user and !$user->photo)) style="display: none" @endif
                                           id="remove_photo"
                                           class="col-lg-12 image-label">{{ trans("users::users.remove_photo") }}</a>
                                    </div>

                                </div>
                                <div class="col-lg-9 col-md-9">

                                    <div class="form-group">
                                        <input name="first_name"
                                               value="{{ @Request::old("first_name", $user->first_name) }}"
                                               class="form-control input-lg"
                                               placeholder="{{ trans("users::users.first_name") }}"/>
                                    </div>

                                    <div class="form-group">
                                        <input name="last_name"
                                               value="{{ @Request::old("last_name", $user->last_name) }}"
                                               class="form-control input-lg"
                                               placeholder="{{ trans("users::users.last_name") }}"/>
                                    </div>

                                </div>
                            </div>

                            <br/>
                            <div class="form-group">
                                <textarea name="about" class="markdown form-control"
                                          placeholder="{{ trans("users::users.about_me") }}"
                                          rows="7">{{ @Request::old("about", $user->about) }}</textarea>
                            </div>

                            @foreach(Action::fire("user.form.featured") as $output)
                                {!! $output !!}
                            @endforeach

                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-default">

                        <div class="panel-body">

                            @if(Auth::user()->can("roles.manage", $user))

                                <div class="row form-group">
                                    <label class="col-sm-3 control-label">{{ trans("users::users.role") }}</label>
                                    <div class="col-sm-9">
                                        <select class="form-control select2 chosen-rtl" name="role_id">

                                            @foreach ($roles as $role)
                                                <option
                                                    @if($user and $user->role_id == $role->id) selected="selected" @endif
                                                    value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>

                                <div class="row form-group">
                                    <label
                                        class="col-sm-3 control-label">{{ trans("users::users.activation") }}</label>
                                    <div class="col-sm-9">

                                        <select class="form-control select2 chosen-rtl" name="status">

                                            @foreach([1 => "activated", 0 => "deactivated"] as $status => $title)
                                                <option
                                                    value="{{ $status }}"
                                                    @if($user and $user->status == $status) selected="selected" @endif
                                                >{{ trans("users::users.".$title) }}</option>
                                            @endforeach

                                        </select>

                                    </div>
                                </div>

                            @else

                                <input type="hidden" name="role_id"
                                       value="{{ $user ? $user->role_id : 0 }}"/>

                                <input type="hidden" name="status"
                                       value="{{ $user ? $user->status : 0 }}"/>

                            @endcan

                            @if(config("i18n.locales"))
                                <div class="row form-group">
                                    <label
                                        class="col-sm-3 control-label">{{ trans("users::users.language") }}</label>
                                    <div class="col-sm-9">
                                        <select class="form-control select2 chosen-rtl" name="lang">

                                            @foreach (config("i18n.locales") as $code => $lang)
                                                <option
                                                    {{  ((($user and $code == $user->lang) or (!$user and $code == app()->getLocale()))) ? 'selected="selected"' : '' }}
                                                    value="{{ $code }}">{{ $lang["title"] }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="lang" value="{{ app()->getLocale() }}"/>
                            @endif

                            <div class="row form-group">
                                <label class="col-sm-3 control-label">{{ trans("users::users.color") }}</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2 chosen-rtl" name="color">

                                        @foreach (["blue", "green"] as $color)
                                            <option
                                                @if($user and $color == $user->color) selected="selected" @endif
                                                value="{{ $color }}">{{ trans("users::users.color_" . $color) }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            @foreach(Action::fire("user.form.sidebar") as $output)
                                {!! $output !!}
                            @endforeach

                        </div>
                    </div>

                    <div class="panel panel-default">

                        <div class="panel-body">

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-facebook"></i></span>
                                <input name="facebook" value="{{ @Request::old("facebook", $user->facebook) }}"
                                       class="form-control input-lg"
                                       placeholder="{{ trans("users::users.facebook") }}"/>
                            </div>

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-twitter "></i></span>
                                <input name="twitter" value="{{ @Request::old("twitter", $user->twitter) }}"
                                       class="form-control input-lg"
                                       placeholder="{{ trans("users::users.twitter") }}"/>
                            </div>

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-google-plus"></i></span>
                                <input name="google_plus"
                                       value="{{ @Request::old("google_plus", $user->google_plus) }}"
                                       class="form-control input-lg"
                                       placeholder="{{ trans("users::users.googleplus") }}"/>
                            </div>

                            <div class="form-group input-group">
                                <span class="input-group-addon"><i class="fa fa-linkedin"></i></span>
                                <input name="linked_in"
                                       value="{{ @Request::old("linked_in", $user->linked_in) }}"
                                       class="form-control input-lg"
                                       placeholder="{{ trans("users::users.linkedin") }}"/>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </form>

@stop

@section("head")

    <style>

        .image-label {
            margin-top: -24px;
        }

    </style>

@stop

@section("footer")

    <script>

        $(document).ready(function () {

            $("#change_photo").filemanager({
                panel: "media",
                types: "png|jpg|jpeg|gif|bmp|image",
                done: function (result, base) {
                    if (result.length) {
                        var file = result[0];
                        $("#user_photo_id").val(file.id);
                        $("#user_photo").attr("src", file.thumbnail);
                    }

                    $("#change_photo").hide();
                    $("#remove_photo").show();
                },
                error: function (media_path) {
                    alert(media_path + "{{ trans("users::users.is_not_an_image") }}");
                }
            });

            $("#remove_photo").click(function () {

                $("#user_photo_id").val(0);
                $("#user_photo").attr("src", "{{ assets("admin::images/user.png") }}");

                $("#remove_photo").hide();
                $("#change_photo").show();

                return false;
            });
        });

    </script>

@stop
