@extends('layouts.app')
@section('title','Admin Lunch Reports')
@section('styles')
    <style>
        body{background-color:#fafafa;}
    </style>
@endsection
@section('content')
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Administrator Reports</h3>
                <br/>
                <label for="selReports" class="marginb1">Report</label>
                <div class="md-form">
                    <select id="selReports" name="selReports" class="custom-select">
                        <option value="0">- Select -</option>
                        <option value="1">Lunch Orders By Provider</option>
                        {{--<option value="2">Lunch Orders By Teacher</option>--}}
                        <option value="2">Lunch Orders By Grade</option>
                        <option value="3">Account Balances</option>
                        <option value="4">Account Details</option>
                        <option value="5">Lunch Labels (Avery 5160)</option>
                    </select>
                </div>
                <div id="grpAccounts" style="display:none;">
                    <label for="selAccounts" class="marginb1">Account</label>
                    <div class="md-form">
                        <select id="selAccounts" class="custom-select">
                            <option value="0">- Select -</option>
                            @foreach($accounts as $account)
                                <option value="{!! $account->id !!}">{!! $account->account_name !!}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="grpDates" style="display:none;">
                    <label for="selDates" class="marginb1">Date</label>
                    <div class="md-form">
                        <select id="selDates" class="custom-select">
                            <option value="0">- Select -</option>
                            @foreach($dates as $date)
                                <option value="{!! $date->provide_date !!}">{!! date_create($date->provide_date)->format('l, F jS, Y') !!}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <hr/>
                <button id="btnGo" type="button" disabled class="btn btn-default waves-effect waves-light"><i
                            class="fa fa-bolt"></i>&nbsp;&nbsp;Run
                </button>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/adminreports.js') }}"></script>
@endsection