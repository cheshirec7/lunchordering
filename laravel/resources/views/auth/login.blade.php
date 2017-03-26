@extends('layouts.app')
@section('title','Login')
@section('styles')
    <style>
        .connectwith {
            text-align: center;
            margin-bottom: 15px;
            font-style: italic;
        }

        .outer_or {
            position: relative;
            margin: 15px 0 40px;
        }

        .inner_or {
            position: absolute;
            top: -10px;
            left: calc(50% - 18px);
            background-color: #fff;
            padding: 0 10px;
            font-style: italic;
        }

        .social_buttons {
            display: flex;
            justify-content: center;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-block">
                    <h3 class="myformheader"><i class="fa fa-lock"></i> Login</h3>
                    @include('partials.notify')
                    <div class="connectwith">Connect with</div>
                    <div class="social_buttons">
                        <form role="form" method="GET" action="{{ url('/auth/fb') }}">
                            <input style="max-height: 40px;" type="image" src="{{ asset('img/facebook-login.png') }}"
                                   alt="Submit"/>
                        </form>
                    </div>
                    <div class="outer_or">
                        <hr/>
                        <div class="inner_or">or</div>
                    </div>

                    <form role="form" method="POST" action="{{ url('/login') }}">
                        {{ csrf_field() }}
                        <div class="md-form">
                            <i class="fa fa-envelope prefix"></i>
                            <input id="email" type="email" class="form-control" placeholder="" name="email"
                                   value="{{ old('email') }}" required autofocus>
                            <label for="email">Your email</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-lock prefix"></i>
                            <input id="password" type="password" class="form-control" name="password" placeholder=""
                                   required>
                            <label for="password">Your password</label>
                        </div>
                        <br/>
                        <fieldset class="form-group">
                            <input type="checkbox" class="filled-in" id="remember" name="remember"
                                   value="1" {{ (old("remember") == 1 ? "checked":"") }}>
                            <label for="remember">Remember Me <i>(do not use on shared computers)</i></label>
                        </fieldset>
                        {{--<hr class="hrfooter"/>--}}
                        <button type="submit" class="btn btn-primary waves-effect waves-light pull-left">Login</button>
                        <div class="pull-right" style="margin-top:15px;"><a href="{{ url('/password/reset') }}">Forgot
                                Password?</a></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection