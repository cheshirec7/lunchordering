@include('reports.header')
<body>
<div id="thereport">
    <img class="logoimg" src="{{ asset('img/logo_orig.png') }}">
    <h2>{!! $title !!}</h2>
    <h3>{!! Auth::user()->account_name !!}</h3>
    <p><i class="date">as of {!! date('l, F jS, Y h:i:s A') !!}</i></p>
    <input type="button" onClick="window.print()" value="Print"/>
    <div class="reportbody">{!! $report !!}</div>
    <p><i>*** Note: [scheduled] orders can be changed or canceled ***</i></p>
</div>
</body>