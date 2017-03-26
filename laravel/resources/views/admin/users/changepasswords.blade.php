@extends('layouts.app')
@section('title','Change Password')
@section('content')
    <div class="container page max400">
        <h2>Change Password</h2>
        {!! Form::open(array('url' => URL::to('admin/changepasswords'), 'method' => 'post', 'files'=> false)) !!}

        <div class="form-group">
            {!! Form::label('accountid', 'Account', array('class' => 'control-label')) !!}
            <select class="form-control" id="accountid" name="accountid">
                <option value="0">- Select -</option>
                <option value="1">Dykstra, Melanie & Randy</option>
                <option value="2">Totten, Eric</option>
                <option value="3">Richmond, Kristy</option>
                <option value="4">Jenner, Bruce</option>
            </select>
        </div>

        <div class="form-group {{ $errors->has('newpassword') ? 'has-error' : '' }}">
            {!! Form::label('newpassword', 'New password', array('class' => 'control-label')) !!}
            {!! Form::password('newpassword', array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('newpassword', ':message') }}</span>
        </div>
        <div class="form-group {{ $errors->has('newpassword_confirmation') ? 'has-error' : '' }}">
            {!! Form::label('newpassword_confirmation', 'Confirm new password', array('class' => 'control-label')) !!}
            {!! Form::password('newpassword_confirmation', array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('newpassword_confirmation', ':message') }}</span>
        </div>
        <div class="form-group text-center btnsubmit">
            <button type="submit" class="btn btn-primary">{!! trans('site/account.submit') !!}</button>
        </div>
        {!! Form::close() !!}
    </div>
@stop
