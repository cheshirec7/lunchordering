@extends('layouts.app')
@section('title','Lunch Report')
@section('styles')
    <style>
        body{background-color:#fafafa;}
    </style>
@endsection
@section('content')
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-block">
                <h3 class="myformheader"><i class="fa fa-file-text"></i> Lunch Report</h3>
                <form action="mylunchreport" target="_blank">
                    <div>
                        <input name="rpttype" type="radio" id="radio0" value="0" checked>
                        <label for="radio0">Show only dates with orders</label>
                    </div>
                    <div>
                        <input name="rpttype" type="radio" id="radio1" value="1">
                        <label for="radio1">Show dates with orders or events</label>
                    </div>
                    <div style="margin-bottom:30px;">
                        <input name="rpttype" type="radio" id="radio2" value="2">
                        <label for="radio2">Show all dates</label>
                    </div>
                    <button id="btnGo" type="submit" class="btn btn-primary waves-effect waves-light"><i
                                class="fa fa-bolt"></i>&nbsp;&nbsp;Run
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection