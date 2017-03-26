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
                    <form role="form" method="POST" action="{{ url('/password/email') }}">
                        {{ csrf_field() }}
                        <div class="md-form">
                            <i class="fa fa-envelope prefix"></i>
                            <input id="email" type="email" class="form-control" placeholder="" name="email"
                                   value="{{ old('email') }}" required autofocus>
                            <label for="email">Your E-Mail Address</label>
                        </div>
                        <br/>
                        <div class="text-xs-center">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Send Password Reset
                                Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection