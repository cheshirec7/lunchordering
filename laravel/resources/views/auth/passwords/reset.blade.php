@extends('layouts.app')
@section('title','Reset Password')
@section('content')
    <div class="row">
        <br/>
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-block">
                    <h3 class="myformheader"><i class="fa fa-lock"></i> Reset Password</h3>
                    @include('partials.notify')
                    <br />
                    <form role="form" method="POST" action="{{ url('/password/reset') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="md-form">
                            <i class="fa fa-envelope prefix"></i>
                            <input id="email" type="email" class="form-control" placeholder="Your email" name="email"
                                   value="{{ $email or old('email') }}" required autofocus>
                            <label for="email">Your email</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-lock prefix"></i>
                            <input id="password" type="password" class="form-control" name="password" placeholder=""
                                   required>
                            <label for="password">New Password</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-lock prefix"></i>
                            <input id="password-confirm" type="password" class="form-control"
                                   name="password_confirmation" placeholder="" required>
                            <label for="password">Confirm new password</label>
                        </div>
                        <br/>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection