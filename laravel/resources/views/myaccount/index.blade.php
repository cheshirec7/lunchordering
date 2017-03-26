@extends('layouts.app')
@section('title', 'My Account')
@section('styles')
    <link href="{{ elixir('css/myaccount.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div id="spinner"></div>
    <div class="row">
        <br/>
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-block">
                    <h3 class="myformheader"><img style="float:left;margin-right: 10px;" id="acctimg" src="{{ asset('img/myaccount.jpg') }}" alt="My Account">My Account</h3>
                    @include('partials.notify')
                    <ul id="myTabs" class="nav nav-tabs tabs-3 red" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#summary" role="tab" id="summary-tab" data-toggle="tab"
                               aria-controls="summary" aria-expanded="true">Summary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#payments" role="tab" id="payments-tab" data-toggle="tab"
                               aria-controls="payments">Payments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#orders" role="tab" id="orders-tab" data-toggle="tab"
                               aria-controls="orders">Orders</a>
                        </li>
                    </ul>
                    <div id="myTabContent" class="tab-content">
                        <div role="tabpanel" class="tab-pane fade show active" id="summary" aria-labelledby="summary-tab">

                            <form role="form" id="formPay" action="/myaccount/pay" method="post" autocomplete="off">
                                {{ csrf_field() }}
                                <input type="hidden" name="credit_amt" value="{!! $total_due !!}"/>
                                <input type="hidden" name="fee" value="{!! $trx_fee !!}"/>

                                <div class="table-responsive">
                                    <table id="tblSummary" class="table table-bordered table-sm">
                                        <thead class="thead-inverse">
                                        <tr>
                                            <th>Totals</th>
                                            <th width="50">Amount</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($order_aggs as $order_agg)
                                            <tr>
                                                <td>{!! $order_agg->first_name !!} {!! $order_agg->last_name !!}
                                                    - {!! $order_agg->order_count !!} Lunches Ordered
                                                </td>
                                                <td>${!! number_format($order_agg->total_price/100,2) !!}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td>{!! $payment_agg->payment_count !!} Payments Received</td>
                                            <td>${!! number_format($payment_agg->credit_amt/100,2) !!}</td>
                                        </tr>
                                        <tr>
                                            <td>PayPal Fees Paid</td>
                                            <td>${!! number_format($fees_paid/100,2) !!}</td>
                                        </tr>

                                        <tr class="curbal">
                                            @if($cur_balance < 5 && $cur_balance > -5)
                                                <td>Current Balance (payments - fees - orders)</td>
                                                <td>$0.00</td>
                                            @elseif($cur_balance < 0)
                                                <td>Current Balance (payments - fees - orders)</td>
                                                <td>${!! number_format(-$cur_balance/100,2) !!}</td>
                                            @else
                                                <td>You Have an Account Credit (payments - fees - orders)</td>
                                                <td>${!! number_format($cur_balance/100,2) !!}</td>
                                            @endif
                                        </tr>

                                        @if($total_due > 100)
                                            <tr>
                                                <td><i>When paying with PayPal (optional), add 2.2% transaction fee</i>
                                                </td>
                                                <td>${!! number_format($trx_fee/100,2) !!}</td>
                                            </tr>
                                            <tr class="paynow">
                                                <td><input class="pull-right" type="image" name="submit"
                                                           src="{{asset ('img/checkout-logo-small.png') }}"/></td>
                                                <td>${!! number_format($total_due/100,2) !!}</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </form>

                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="payments" aria-labelledby="payments-tab">
                            <div class="table-responsive">
                                <table id="tblPayments" class="table table-striped table-bordered table-sm"
                                       style="margin-top:0 !important">
                                    <thead class="thead-inverse">
                                    <tr>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Received On</th>
                                        <th>Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="orders" aria-labelledby="orders-tab">
                            @if (count($order_aggs) > 0)
                                <select id="orderees" class="custom-select">
                                    @foreach ($order_aggs as $order_agg)
                                        <option value="{!! $order_agg->user_id !!}">{!! $order_agg->first_name !!} {!! $order_agg->last_name !!}</option>
                                    @endforeach
                                </select>
                            @endif
                            <div class="table-responsive">
                                <table id="tblOrders" class="table table-striped table-bordered table-sm">
                                    <thead class="thead-inverse">
                                    <tr>
                                        <th>Date</th>
                                        <th>Lunch Ordered</th>
                                        <th>Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/myaccount.js') }}"></script>
@endsection


