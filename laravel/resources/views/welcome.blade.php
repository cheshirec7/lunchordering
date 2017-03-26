@extends('layouts.app')
@section('title','Welcome')
@section('content')
    @include('partials.notify')
    <img class="pull-right" style="margin: 0 0 15px 15px" alt="lunchbag" src="{{ asset('img/lunch124x83.jpg') }}">
    <h2>Welcome to the CCA Lunch Ordering System</h2>
    <br/><br/>
    @if (Auth::guest())
        <h3>Returning Users</h3>

        {{--@if ($isPhone)--}}
            {{--<p>--}}
                {{--<a href="https://lod-m.erictotten.info/#/orders">--}}
                    {{--<button style="width:200px;" class="btn btn-primary waves-effect waves-light">--}}
                        {{--View My Orders (for mobile phones)--}}
                    {{--</button>--}}
                {{--</a>--}}
            {{--</p>--}}
            {{--<p style="margin-left:85px;font-style:italic;">- or -</p>--}}
            {{--<p>--}}
                {{--<a style="margin-left:20px;" href="{{ URL::to('orders') }}">--}}
                    {{--View My Orders (All Devices)--}}
                {{--</a>--}}
            {{--</p>--}}
        {{--@else--}}
        <p>
            <a href="{{ URL::to('orders') }}">
                <button class="btn btn-primary waves-effect waves-light">
                    View My Orders
                </button>
            </a>
        </p>
        {{--@endif--}}

        <br/>
        <h3>New User or Forgot Your Password?</h3>
        <p>
            <a href="{{ URL::to('password/reset') }}">
                <button class="btn btn-primary waves-effect waves-light">
                    Reset My Password
                </button>
            </a>
        </p>
    @else
        <h3>You Are Logged In</h3>
        <p class="text-success">** {{ Auth::user()->account_name }} **</p>
        <p>
            <a href="{{ URL::to('orders') }}">
                <button class="btn btn-primary waves-effect waves-light">
                    View My Orders
                </button>
            </a>
        </p>
    @endif
    <br/>
    <h3>Payments</h3>
    <p>
        Payments can be made in the following ways:
    </p>
    <ul class="mylist">
        <li>
            You can use PayPal to pay for lunch on this website, or
        </li>
        <li>
            Your tuition bill will reflect your balance due at the end of the month.
        </li>
    </ul>
    <br/>
    <h3>About The Lunch Ordering System</h3>
    <p>
        This system works in "real-time", which means that you can add or delete lunch orders up until the time
        orders are placed with our vendors. So for example if you place an order but then your child will end up
        missing school and therefore that lunch order, you will be able to cancel the order if it has not already
        been placed with the vendor.
    </p>
    <p>
        Questions? Problems? Please feel free to <a href="{{ URL::to('contact') }}">contact us</a>.
    </p>
    <p>
        Thanks for using the Lunch Ordering System!
    </p>
    <p>
        <em>The CCA Lunch Ordering Team</em>
    </p>
@endsection

