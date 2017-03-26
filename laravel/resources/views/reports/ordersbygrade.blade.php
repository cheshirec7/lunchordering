@include('reports.header')
<body>
<style>
    .square {
        height: 10px;
        width: 10px;
        border: 1px solid #999;
    }
</style>
<div id="thereport">
    <img class="logoimg" src="{{ asset('img/logo_orig.png') }}">
    <h2>{!! $title !!}</h2>
    <h3>{!! $thedate !!}</h3>
    <img style="height:50px;" src="/img/providers/{!! $provider->provider_image !!}"
         alt="{!! $provider->provider_name !!}">
    <br/><br/>
    <input type="button" onClick="window.print()" value="Print"/>
    <div class="reportbody">
        <?php $grade_desc = ''; ?>
        @foreach($items as $item)
            @if ($grade_desc != $item->grade_desc)
                @if ($grade_desc != '')
                    </table><br/>
                @endif
                <table>

                @if ($item->grade_desc == '(unassigned)')
                    <tr>
                        <th colspan="3" class="borderbottom" style="text-align:left;">No grade assigned</th>
                    </tr>
                @else
                    <tr>
                        <th colspan="3" class="borderbottom" style="text-align:left;">{!! $item->grade_desc !!}</th>
                    </tr>
                @endif
                <?php $grade_desc = $item->grade_desc; ?>
            @endif

            <tr>
                <td width="10">
                    <div class="square"></div>
                </td>
                <td width="150">{!! $item->first_name !!} {!! $item->last_name !!}</td>
                <td>{!! $item->short_desc !!}</td>
            </tr>
        @endforeach
        </table>
    </div>
    <p><i class="date">as of {!! date('l, F jS, Y h:i:s A') !!}</i></p>
</div>
</body>