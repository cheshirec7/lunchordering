@include('reports.ab_header')
<body>
<div id="thereport">
    <img class="logoimg" src="{{ asset('img/logo_orig.png') }}">
    <h2>{!! $title !!}</h2>
    <p><i class="date">as of {!! date('l, F jS, Y h:i:s A') !!}</i></p>
    <input type="button" onClick="window.print()" value="Print"/>
    <div class="reportbody">
        <table>
            <tr>
                <th>Account</th>
                <th># of Orders</th>
                <th>Credits</th>
                <th>Debits</th>
                <th>Fees</th>
                <th>Balance</th>
            </tr>
            @foreach($items as $item)
                <tr>
                    <td>{!! $item->account_name !!}</td>
                    <td>{!! $item->total_orders !!}</td>
                    <td>${!! number_format($item->confirmed_credits/100,2) !!}</td>
                    {{--<td>${!! number_format($item->confirmed_debits/100,2) !!}</td>--}}
                    <td>${!! number_format($item->total_debits/100,2) !!}</td>
                    <td>${!! number_format($item->fees/100,2) !!}</td>
                    {{--<td>${!! number_format( ($item->confirmed_credits - $item->fees - $item->total_debits )/100,2) !!}</td>--}}
                    @if($item->confirmed_credits - $item->total_debits - $item->fees < 0)
                        <td>(${!! number_format(-($item->confirmed_credits - $item->total_debits - $item->fees)/100,2) !!})</td>
                    @else
                        <td>${!! number_format(($item->confirmed_credits - $item->total_debits - $item->fees)/100,2) !!}</td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>
</div>
</body>