@extends('layouts.app')
@section('title','Receive Payments')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }

        #modalPayment .modal-dialog {
            margin: 80px auto 30px;
            width: 400px;
        }

        #tablePayments td:nth-child(3), #tablePayments td:nth-child(4) {
            text-align: right;
        }

        #paymentBalanceText {
            text-align: right;
            float: right;
        }

        #paymentBalanceText div {
            font-weight: bold;
            border: 1px solid #ccc;
            background-color: #FFFACD;
            border-radius: 2px;
            padding: 5px 10px;
            display: inline-block;
        }

        #paymentBalanceText .balancedue {
            color: #cc0000
        }

        #paymentBalanceText .creditavailable {
            color: #5cb85c
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>

    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i>&nbsp;Receive Payments</h3>
                <br/>
                <label for="select_account">Account</label>
                <select class="custom-select" name="select_account" id="select_account">
                    <option value="0">- Select -</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </select>
                <div class="addeditdeletebuttons">
                    <button id="btnNew" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                class="fa fa-plus"></i>&nbsp;New
                    </button>
                    <div class="btn-group">
                        <button id="btnEdit" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-edit"></i>&nbsp;Edit
                        </button>
                        <button id="btnDel" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-remove"></i>&nbsp;Del
                        </button>
                    </div>
                </div>
                <table id="tablePayments" class="table table-striped table-bordered table-hover editable table-sm">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th width="75">Amount</th>
                        <th>Received</th>
                        <th style="display:none">pay_method_int</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div id="paymentBalanceText"></div>
            </div>
        </div>
    </div>

    <div id="modalPayment" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header"></div>
                <form role="form" id="formPayment" autocomplete="off">
                    <div class="modal-body">
                        <div class="entityheader">
                            <label>Account</label>
                            <div id="account_name"></div>
                        </div>
                        <div class="msgAlert"></div>

                        <label for="credit_date" style="margin: 0 0 0 3rem">Date</label>
                        <div class="md-form input-group">
                            <i class="fa fa-calendar prefix textRed"></i>
                            <div style="margin: 0 0 0 3rem">
                                <div class="input-group date" id="datetimepicker1">
                                    <input type="text" class="form-control" id="credit_date" name="credit_date"/>
                                    <span class="input-group-addon"><i class="fa fa-edit"></i></span>
                                </div>
                            </div>
                        </div>

                        {{--<label for="credit_date">Date</label>--}}
                        {{--<div class="md-form input-group">--}}
                        {{--<div class="input-group date" id="datetimepicker1">--}}
                        {{--<input type="text" class="form-control" id="credit_date" name="credit_date"/>--}}
                        {{--<span class="input-group-addon"><i class="fa fa-calendar"></i></span>--}}
                        {{--</div>--}}
                        {{--</div>--}}

                        <label for="pay_method" style="margin-left:3rem">Payment Method</label>
                        <div class="md-form">
                            <i class="fa fa-list-ul prefix textRed"></i>
                            <select id="pay_method" name="pay_method" class="custom-select">
                                <option value="0">- Select -</option>
                                <option value="1">Cash</option>
                                <option value="2">Check</option>
                                <option value="3">PayPal</option>
                                <option value="4">Adjustment</option>
                            </select>
                        </div>
                        <br/>

                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="credit_desc" name="credit_desc"/>
                            <label for="credit_desc">Description (Check #, Adj. Reason, etc.)</label>
                        </div>

                        <br/>
                        <div class="md-form">
                            <i class="fa fa-dollar prefix"></i>
                            <input type="number" min="0" max="10" step="0.01" data-number-to-fixed="2"
                                   data-number-stepfactor="100"
                                   class="form-control currency" name="credit_amt" id="credit_amt" required/>
                            <label for="credit_amt">Amount</label>
                        </div>

                        <input type="hidden" name="pay_id" value="0"/>
                        <input type="hidden" name="account_id" value="0"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel
                        </button>
                        <button id="btnSave" type="button" class="btn btn-primary btn-sm">&nbsp;Save&nbsp;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/payments.js') }}"></script>
    {{--<script src="/js/payments.js"></script>--}}
@endsection
