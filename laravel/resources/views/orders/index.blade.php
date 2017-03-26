@extends('layouts.app')
@section('title','Order Lunches')
@section('styles')
    <link href="{{ elixir('css/orderlunches.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div id="spinner"></div>
    @include('partials.notify')
    <div class="orderlunches">
        <table class="calheader">
            <tr>
                <td>
                    <table class="pull-left">
                        <tr>
                            <td><img class="navbtn prev" src="{{ asset('img/back.png') }}" alt="back"></td>
                            <td><img class="navbtn next" src="{{ asset('img/next.png') }}" alt="next"></td>
                            <td>
                                <div id="perioddesc">This week</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="pull-right">
                        <tr>
                            @if($isUser)
                                <td id="amountduetext">Account<br>Balance</td>
                                <td id="amountdue">{!! $amountDue !!}</td>
                                <td><a href="{{ url('/myaccount') }}"><img id="accountimg" src="/img/myaccount.png"
                                                                           alt="My Account" title="My Account"></a></td>
                                <td>
                                    <select id="accountnames" name="accountnames" class="hide">
                                        <option value="{!! Auth::id() !!}">{{ Auth::user()->account_name }}</option>
                                    </select>
                                </td>
                            @else
                                <td id="orderlunchfor">Lunch<br>Orders For</td>
                                <td>
                                    <select id="accountnames" name="accountnames" class="custom-select">
                                        @foreach ($accounts as $account)
                                            <option {{ $account->id == $accid ? 'selected' : '' }} value="{{ $account->id }}">{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div id="caroLunches" class="carousel slide" data-ride="carousel" data-wrap="false" data-interval="false">
            <div class="carousel-inner" role="listbox">
                <div class="carousel-item active">
                    {!! $lunchestableweek !!}
                </div>
            </div>
        </div>
    </div>
    <div class="todaytext">Today is <a class="returntotoday" href="#">{{ date('l, F jS, Y') }}</a></div>

    <div id="modalOrderLunch" class="modal fade" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="orderform">
                    <div id="modal-header-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm waves-effect pull-left" data-dismiss="modal">
                            Cancel
                        </button>
                        <button id="btnSave" type="submit" class="btn btn-primary btn-sm waves-effect">OK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/orderlunches.js') }}"></script>
@endsection


