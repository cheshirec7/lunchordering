<div class="modal-header">
    <a class="provimg" target="_blank" href="{!! $lunchdate->provider_url !!}">
        <img src="/img/providers/{!! $lunchdate->provider_image !!}" alt="{!! $lunchdate->provider_name !!}"
             title="{!! $lunchdate->provider_name !!}"">
    </a>
    <div id="lunchorder">Order Lunches</div>
    <div id="lunchdate">{!! $thedate->format('l, M j, Y') !!}<br/>{!! $user->first_name !!} {!! $user->last_name !!}
    </div>
</div>
<div class="modal-body">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td></td>
            <td width="45">Qty</td>
        </tr>
        @foreach ($menuitems as $menuitem)
            <?php
            $qty = 0;
            foreach ($orderdetails as $orderdetail) {
                if ($orderdetail->menuitem_id == $menuitem->menuitem_id) {
                    $qty = $orderdetail->qty;
                    break;
                }
            }
            ?>
            <tr>
                <td>
                    <input type="checkbox" {{ $qty > 0 ? ' checked ' : '' }}class="filled-in"
                           id="chk{{ $menuitem->menuitem_id }}" data-id="item{{ $menuitem->menuitem_id }}">
                    <label for="chk{{ $menuitem->menuitem_id }}">{{ $menuitem->item_name }} {{ $menuitem->price != config('app.menuitem_default_price') ? ' ($'.number_format($menuitem->price/100,2).')' : '' }}</label>
                </td>
                <td>
                    <input type="number" data-price="{{ $menuitem->price }}" name="item{{ $menuitem->menuitem_id }}"
                           min="1" max="2" {{ $qty == 0 ? 'disabled' : '' }} value="{{ $qty > 0 ? $qty : '' }}">
                </td>
            </tr>
        @endforeach
    </table>
    <div id="ordertotaltext">Order Total
        <div id="ordertotal" class="pull-right">${!! number_format($totalprice/100,2) !!}&nbsp;</div>
    </div>
    @if ($lunchdate->provider_includes)
        <div id="lunchincludes">- {{ $lunchdate->provider_includes }} -</div>
    @endif
</div>