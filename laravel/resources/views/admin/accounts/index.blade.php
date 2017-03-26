@extends('layouts.app')
@section('title', 'Accounts')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }

        #tableAccounts td {
            cursor: pointer
        }

        #tableAccounts td:nth-child(3),
        #tableAccounts td:nth-child(4) {
            text-align: center;
        }

        #tableAccounts td:nth-child(5),
        #tableAccounts td:nth-child(6) {
            text-align: right;
        }

        #tableAccounts_filter input {
            width: 300px;
            padding: 0;
            margin: 0 0 0 10px;
        }

        #modalAccount .modal-dialog {
            margin: 150px auto 30px;
            width: 450px;
        }

        .addeditdeletebuttons {
            margin-bottom: 2px;
        }

        .vertline {
            border-left: 1px solid #ddd;
            margin-left: 1px;
            height: 100%;
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>

    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Accounts</h3>
                <div class="addeditdeletebuttons" style="margin-bottom: 2px">
                    <button id="btnNew" type="button" class="btn btn-new btn-sm"><i class="fa fa-plus"></i>&nbsp;New
                    </button>
                    <div class="btn-group">
                        <button id="btnEdit" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-edit"></i>&nbsp;Edit
                        </button>
                        <button id="btnDel" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-remove"></i>&nbsp;Del
                        </button>
                    </div>
                    <span class="vertline"></span>
                    <button id="btnViewUsers" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                class="fa fa-users"></i>&nbsp;Users
                    </button>
                </div>
                <table id="tableAccounts" class="table table-striped table-bordered table-hover table-sm editable">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Account Name</th>
                        <th>Email</th>
                        <th>#Users</th>
                        <th>#Orders</th>
                        <th>Credits</th>
                        <th>Debits</th>
                        <th>FBUSERID</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="modalAccount" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form role="form" id="formAccount" autocomplete="off">
                    <div class="modal-header"></div>
                    <div class="modal-body">
                        <div class="msgAlert"></div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="account_name" name="account_name"
                                   maxlength="255" required="required"
                                   placeholder="(last name, parent first names)"/>
                            <label for="account_name">Account Name</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="email" class="form-control" id="email" name="email" maxlength="255"
                                   required="required"/>
                            <label for="email">Email</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="fbuserid" name="fbuserid" maxlength="50"/>
                            <label for="fb">Facebook User ID</label>
                        </div>
                        <input type="hidden" name="account_id" value="0"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel
                        </button>
                        <button id="btnSave" type="button" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/accounts.js') }}"></script>
@endsection
