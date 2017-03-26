@include('reports.header')
<body>
<div id="thereport">
    <img class="logoimg" src="{{ asset('img/logo_orig.png') }}">
    <h2>{!! $title !!}</h2>
    <h3>{!! $thedate !!}</h3>
    <img style="height:50px;" src="/img/providers/{!! $provider->provider_image !!}"
         alt="{!! $provider->provider_name !!}">
    <br/><br/>
    <input type="button" onClick="window.print()" value="Print"/>
    <div class="reportbody">
        <table>
            <tr>
                <th class="borderbottom">Qty</th>
                <th class="borderbottom" style="text-align:left;">Item</th>
            </tr>
            @foreach($items as $item)
                <tr>
                    <td width="30">{!! $item->qty !!}</td>
                    <td>{!! $item->item_name !!}</td>
                </tr>
            @endforeach
        </table>
    </div>
    <p><i class="date">as of {!! date('l, F jS, Y h:i:s A') !!}</i></p>
</div>
</body>
