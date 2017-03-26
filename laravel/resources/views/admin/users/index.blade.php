@extends('layouts.app')
@section('title', 'Users')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }

        #tableUsers td:nth-child(5),
        #tableUsers td:nth-child(6),
        #tableUsers td:nth-child(7) {
            text-align: center;
        }

        #modalUser .modal-dialog {
            margin: 65px auto 30px;
            width: 400px;
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>

    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Users</h3>
                <br/>
                <label for="select_account">Account </label>
                <select class="custom-select" name="select_account" id="select_account">
                    <option value="0">- Show All Users for All Accounts -</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </select>
                <div class="addeditdeletebuttons">
                    <button id="btnNew" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                class="fa fa-plus"></i> New
                    </button>
                    <div class="btn-group">
                        <button id="btnEdit" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-edit"></i> Edit
                        </button>
                        <button id="btnDel" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-remove"></i> Del
                        </button>
                    </div>
                </div>
                <table id="tableUsers" class="table table-striped table-bordered table-hover editable table-sm">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Teacher</th>
                        <th>Grade</th>
                        <th width="75">Type</th>
                        <th width="110">Can Order</th>
                        <th class="hidden">user_type_int</th>
                        <th class="hidden">allowed_to_order_int</th>
                        <th class="hidden">gradelevel_id</th>
                        <th class="hidden">teacher_id</th>
                        <th class="hidden">account_id</th>
                        <th class="hidden">account_name</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalUser" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form role="form" id="formUser" autocomplete="off">
                    <div class="modal-header"></div>
                    <div class="modal-body">
                        <div class="entityheader">
                            <label>Account</label>
                            <div id="account_name"></div>
                        </div>

                        <div class="msgAlert"></div>
                        <br/>

                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="first_name" name="first_name" required/>
                            <label for="first_name">First Name</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="last_name" name="last_name" required/>
                            <label for="last_name">Last Name</label>
                        </div>

                        <div style="padding-left:3em;">
                            <label for="user_type" class="marginb1">Type</label>
                            <div class="md-form">
                                <select id="user_type" name="user_type" class="custom-select">
                                    <option value="3">Student</option>
                                    <option value="4">Teacher</option>
                                    <option value="5">Staff</option>
                                    <option value="6">Parent</option>
                                    <option value="2">Admin</option>
                                </select>
                            </div>

                            <div id="fg_grades">
                                <label for="grade_id" class="marginb1">Grade</label>
                                <div class="md-form">
                                    <select id="grade_id" name="grade_id" class="custom-select">
                                        <option value="1">[ Select ]</option>
                                        @foreach ($gradelevels as $grade)
                                            <option value="{{ $grade->DT_RowId }}">{{ $grade->grade_desc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="fg_teachers">
                                <label for="teacher_id" class="marginb1">Teacher</label>
                                <div class="md-form">
                                    <select id="teacher_id" name="teacher_id" class="custom-select">
                                        <option value="1">[ Select ]</option>
                                    </select>
                                </div>
                            </div>

                            <div id="fg_allowedtoorder">
                                <label for="allowed_to_order" class="marginb1">Can Order</label>
                                <div class="md-form">
                                    <select id="allowed_to_order" name="allowed_to_order" class="custom-select">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="account_id" value="0"/>
                        <input type="hidden" name="user_id" value="0"/>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal"
                                tabindex="-1">
                            Cancel
                        </button>
                        <button id="btnSave" type="button" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/users.js') }}"></script>
    {{--<script src="/js/users.js"></script>--}}
@endsection
