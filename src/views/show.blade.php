@extends("admin::layouts.master")

@section("content")
    <div class="row wrapper border-bottom white-bg page-heading">

        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">

            <h2>
                <i class="fa fa-users"></i>
                {{ trans("users::users.users") }}
            </h2>

            <ol class="breadcrumb">
                <li>
                    <a href="{{ route("admin") }}">{{trans("admin::common.admin") }}</a>
                </li>
                <li>
                    <a href="{{ route("admin.users.show") }}">
                        {{ trans("users::users.users") }}
                        ({{ $users->total() }})</a>
                </li>
            </ol>

        </div>

        <div class="col-lg-8 col-md-6 col-sm-6 col-xs-12 text-right">

            @can("users.create")

                <a href="{{ route("admin.users.create") }}" class="btn btn-primary btn-labeled btn-main">
                    <span class="btn-label icon fa fa-plus"></span>
                    {{ trans("users::users.add_new") }}
                </a>

            @endcan

        </div>

    </div>

    <div class="wrapper wrapper-content fadeInRight">

        <div id="content-wrapper">

            @include("admin::partials.messages")

            <form action="" method="get" class="filter-form">

                <input type="hidden" name="per_page" value="{{ Request::get('per_page') }}"/>

                <div class="row">
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">

                            <select name="sort" class="form-control chosen-select chosen-rtl">

                                @foreach(['created_at', 'first_name'] as $field)
                                    <option
                                        value="{{ $field }}" {{ $sort == $field ? 'selected="selected"' : '' }}>{{ trans("users::users.attributes.".$field) }}</option>
                                @endforeach

                            </select>

                            <select name="order" class="form-control chosen-select chosen-rtl">

                                @foreach(['asc', 'desc'] as $direction)
                                    <option
                                        value="{{ $direction }}" {{  $order == $direction ? 'selected="selected"' : '' }}>{{ trans("users::users.".$direction) }}</option>
                                @endforeach

                            </select>

                            <button type="submit"
                                    class="btn btn-primary">{{ trans("users::users.order") }}</button>

                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <div class="form-group">

                            <select name="status" class="form-control chosen-select chosen-rtl">
                                <option value="">{{ trans("users::users.all") }}</option>
                                <option {{  Request::get("status") == "1" ? 'selected="selected"' : '' }}
                                        value="1">{{ trans("users::users.activated") }}</option>
                                <option {{ Request::get("status") == "0" ? 'selected="selected"' : '' }}
                                        value="0">{{ trans("users::users.deactivated") }}</option>
                            </select>

                            <select name="role_id" class="form-control chosen-select chosen-rtl">

                                <option value="">{{ trans("users::users.all_roles") }}</option>

                                @foreach ($roles as $role)
                                    <option {{ $role->id == Request::get("role_id") ? 'selected="selected"' : '' }}
                                            value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach

                            </select>

                            <button type="submit"
                                    class="btn btn-primary">{{ trans("users::users.filter") }}</button>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4">
                        <form action="" method="get" class="search_form">
                            <div class="input-group">
                                <input name="q" value="{{ Request::get("q") }}" type="text"
                                       class=" form-control"
                                       placeholder="{{ trans("users::users.search_users") }} ...">
                                <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
                        </span>
                            </div>
                        </form>
                    </div>
                </div>
            </form>

            <form method="post" class="action_form">

                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                <div class="ibox float-e-margins">

                    <div class="ibox-title">
                        <h5> {{ trans("users::users.users") }} </h5>
                    </div>

                    <div class="ibox-content">

                        @if (count($users))

                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 action-box">

                                    <select name="action" class="form-control pull-left">

                                        <option value="-1"
                                                selected="selected">{{ trans("users::users.bulk_actions") }}</option>

                                        @can("users.delete")
                                            <option value="delete">{{ trans("users::users.delete") }}</option>
                                        @endcan

                                    </select>

                                    <button type="submit"
                                            class="btn btn-primary pull-right">{{ trans("users::users.apply") }}</button>

                                </div>

                                <div class="col-lg-6 col-md-4 hidden-sm hidden-xs"></div>

                                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
                                    <select class="pull-left form-control per_page_filter">

                                        <option value="" selected="selected">
                                            -- {{ trans("users::users.per_page") }} --
                                        </option>

                                        @foreach([10, 20, 30, 40] as $num)
                                            <option
                                                value="{{ $num }}" {{ $num == $per_page ? 'selected="selected"' : '' }}>{{ $num }}</option>
                                        @endforeach

                                    </select>
                                </div>

                            </div>

                            <div class="table-responsive">

                                <table cellpadding="0" cellspacing="0" border="0"
                                       class="table table-striped table-hover">

                                    <thead>
                                    <tr>

                                        <th style="width:35px"><input type="checkbox" class="i-checks check_all"
                                                                      name="ids[]"/>
                                        </th>
                                        <th style="width:50px">{{ trans("users::users.photo") }}</th>
                                        <th>{{ trans("users::users.name") }}</th>
                                        <th>{{ trans("users::users.email") }}</th>
                                        <th>{{ trans("users::users.created") }}</th>
                                        <th>{{ trans("users::users.role") }}</th>
                                        <th>{{ trans("users::users.actions") }}</th>
                                    </tr>
                                    </thead>

                                    <tbody>

                                    @foreach ($users as $user)

                                        <tr>

                                            <td>
                                                <input type="checkbox" class="i-checks" name="id[]"
                                                       value="{{ $user->id }}"/>
                                            </td>

                                            <td>
                                                @if($user->photo)
                                                    <img class="img-rounded" style="width:50px"
                                                         src="{{ thumbnail($user->photo->path) }}"/>
                                                @else
                                                    <img class="img-rounded"
                                                         src="{{ assets("admin::images/user.png") }}"/>
                                                @endif
                                            </td>

                                            <td>

                                                @can("users.edit", $user)
                                                    <a class="text-navy"
                                                       href="{{ route("admin.users.edit", ["id" => $user->id]) }}">
                                                        <strong> {{ $user->name }} </strong>
                                                    </a>
                                                @else
                                                    <strong> {{ $user->name }} </strong>
                                                @endcan

                                            </td>

                                            <td>
                                                <small>
                                                    {{ empty($user->email) == "" ? $user->email: "-" }}
                                                </small>
                                            </td>

                                            <td>
                                                <small>
                                                    {{ $user->created_at->render() }}
                                                </small>
                                            </td>

                                            <td>
                                                <small>
                                                    {{ $user->role ? $user->role->name : trans("users::users.no_role") }}
                                                </small>
                                            </td>

                                            <td class="center">

                                                @can("users.edit", $user)

                                                    <a href="{{ route("admin.users.edit", ["id" => $user->id]) }}">
                                                        <i class="fa fa-pencil text-navy"></i>
                                                    </a>

                                                @endcan

                                                @can("users.delete", $user)

                                                    <a class="delete_user ask"
                                                       message="{{ trans("users::users.sure_delete") }}"
                                                       href="{{ route("admin.users.delete", ["id" => $user->id]) }}">
                                                        <i class="fa fa-times text-navy"></i>
                                                    </a>

                                                @endcan

                                            </td>
                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                            </div>

                            <div class="row">

                                <div class="col-lg-12 text-center">
                                    {{ trans("users::users.page") }}
                                    {{ $users->currentPage() }}
                                    {{ trans("users::users.of") }}
                                    {{ $users->lastPage() }}
                                </div>

                                <div class="col-lg-12 text-center">
                                    {{ $users->appends(Request::all())->render() }}
                                </div>

                            </div>

                        @else
                            {{ trans("users::users.no_records") }}
                        @endif

                    </div>
                </div>
            </form>

        </div>
    </div>

@stop

@section("footer")

    <script>

        $(document).ready(function () {

            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });

            $('.check_all').on('ifChecked', function (event) {
                $("input[type=checkbox]").each(function () {
                    $(this).iCheck('check');
                    $(this).change();
                });
            });

            $('.check_all').on('ifUnchecked', function (event) {
                $("input[type=checkbox]").each(function () {
                    $(this).iCheck('uncheck');
                    $(this).change();
                });
            });

            $('.delete_user').click(function (event) {
                var self = $(this);
                var user_id = $(this).attr('data-id');
                $("#current_user_id").val(user_id);
                $('#all_users_delete option').prop('disabled', false);
                $('#all_users_delete option[value=' + user_id + ']').prop('disabled', true);
            });

            $(".filter-form input[name=per_page]").val($(".per_page_filter").val());

            $(".per_page_filter").change(function () {
                var base = $(this);
                var per_page = base.val();
                $(".filter-form input[name=per_page]").val(per_page);
                $(".filter-form").submit();
            });

        });

    </script>

@stop
