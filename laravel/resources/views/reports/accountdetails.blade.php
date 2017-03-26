@include('reports.header')
<body>
<div id="thereport">
    <img class="logoimg" src="{{ asset('img/logo_orig.png') }}">
    <h2>{!! $title !!}</h2>
    <h3>{!! $account->account_name !!}</h3>
    <p><i class="date">as of {!! date('l, F jS, Y h:i:s A') !!}</i></p>
    <input type="button" onClick="window.print()" value="Print"/>
    <div class="reportbody">
        <h2 class="borderbottom">Summary</h2>
        <table>
            <tr>
                <th># of Orders</th>
                <th>Credits</th>
                <th>Debits-To-Date</th>
                <th>Total Debits</th>
                <th>Fees</th>
                <th>Balance</th>
            </tr>
            <tr>
                <td>{!! $account->total_orders !!}</td>
                <td>${!! number_format($account->confirmed_credits/100,2) !!}</td>
                <td>${!! number_format($account->confirmed_debits/100,2) !!}</td>
                <td>${!! number_format($account->total_debits/100,2) !!}</td>
                <td>${!! number_format($account->fees/100,2) !!}</td>
                @if($account->confirmed_credits - $account->total_debits - $account->fees < 0)
                    <td>
                        -${!! number_format(-($account->confirmed_credits - $account->total_debits - $account->fees)/100,2) !!}</td>
                @else
                    <td>
                        ${!! number_format(($account->confirmed_credits - $account->total_debits - $account->fees)/100,2) !!}</td>
                @endif
            </tr>
        </table>
        <br/>
        <h2 class="borderbottom">Payments</h2>
        <table>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Fee</th>
                <th>Received</th>
            </tr>
            @foreach($payments as $payment)
                <tr>
                    @if ($payment->pay_method == 1)
                        <td>Cash</td>
                    @elseif ($payment->pay_method == 2)
                        <td>Check</td>
                    @elseif ($payment->pay_method == 3)
                        <td>PayPal</td>
                    @elseif ($payment->pay_method == 4)
                        <td>Adjustment</td>
                    @else
                        <td>Invalid Type</td>
                    @endif
                    <td>{!! $payment->credit_desc !!}</td>
                    <td>${!! number_format($payment->credit_amt/100,2) !!}</td>
                    <td>${!! number_format($payment->fee/100,2) !!}</td>
                    <td>{!! $payment->created_at !!}</td>
                </tr>
            @endforeach
        </table>
        <br/>
        <h2 class="borderbottom">Orders</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Name</th>
                <th>Order</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Status</th>
            </tr>
            @foreach($orders as $order)
                <tr>
                    <td>{!! $order->order_date !!}</td>
                    <td>{!! $order->last_name !!}, {!! $order->first_name !!}</td>
                    <td>{!! $order->item_name !!}</td>
                    <td>{!! $order->qty !!}</td>
                    <td>${!! number_format($order->price/100,2) !!}</td>
                    @if ($order->status_code == 0)
                        <td>Scheduled</td>
                    @elseif ($order->status_code == 1)
                        <td>Ordered</td>
                    @elseif ($order->status_code == 2)
                        <td>Canceled</td>
                    @elseif ($order->status_code == 3)
                        <td>Transferred</td>
                    @else
                        <td>Invalid Type</td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>
</div>
</body>