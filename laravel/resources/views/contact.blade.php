@extends('layouts.app')
@section('title','Contact Us')
@section('content')
<div class="row">
    <br/>
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-block">
                <h3 class="myformheader"><i class="fa fa-envelope"></i> Contact Us</h3>
                @include('partials.notify')
                <br />
                <form action="{{ url('contact') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="md-form">
                        <i class="fa fa-user prefix"></i>
                        <input class="form-control" pattern="[A-Z,a-z, ]*" type="text" id="name" name="name"
                               value="{{ old('name') }}" required autofocus>
                        <label for="name">Your name</label>
                    </div>
                    <br />
                    <div class="md-form">
                        <i class="fa fa-envelope prefix"></i>
                        <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}"
                               required>
                        <label for="email">Your email</label>
                    </div>
                    <br />
                    <div class="md-form">
                        <i class="fa fa-pencil prefix"></i>
                        <textarea class="md-textarea" type="text" rows="5" id="message" name="message"
                                  required>{{ old('message') }}</textarea>
                        <label for="message">Message</label>
                    </div>

                    <div class="md-form captcha">
                        {!! Recaptcha::render() !!}
                        {{--<span class="help-block">{{ $errors->first('g-recaptcha-response', 'Please ensure that you are a human!') }}</span>--}}
                    </div>

                    <fieldset class="form-group">
                        <input type="checkbox" id="sendcopy" name="sendcopy"
                               value="1" {{ (old("sendcopy") == 1 ? "checked":"") }}>
                        <label for="sendcopy">Send a copy to my email address</label>
                    </fieldset>

                    <hr class="hrfooter"/>

                    <div class="text-xs-center">
                        <button class="btn btn-ins btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
