<div class="modal-header">
    <div id="modaltitle">Lunch Scheduling</div>
    <div id="scheduledate">{!! $thedate->format('l, F j, Y') !!}</div>
</div>
<div class="modal-body">
    <div class="form-group">
        <label class="margin0">Provider</label>@if($orders_placed) <i> (Locked - orders have been
            placed)</i>@elseif($has_orders) <i> (Locked - orders exist)</i> @endif
        <select id="select_provider" style="width:100%" name="select_provider" class="custom-select"
                @if($orders_placed || $has_orders) disabled @endif>
            <option value="0">[No Provider Selected]</option>
            <option @if($selected_provider_id==1) selected @endif value="1">No Lunch (No School)</option>
            <option @if($selected_provider_id==2) selected @endif value="2">No Lunch (Early Dismissal)</option>
            <option @if($selected_provider_id==3) selected @endif value="3">Lunch Provided</option>
            @foreach($providers as $provider)
                <option @if($selected_provider_id==$provider->id) selected
                        @endif value="{!! $provider->id !!}">{!! $provider->provider_name !!}</option>
            @endforeach
        </select>
    </div>
    <div id="menuitemscontainer" @if($menuitems == '') class="hide" @endif>
        <label class="margin0">Lunches Available</label>
        <div class="form-group">
            <div class="scrollbox">
                {!! $menuitems !!}
            </div>
        </div>
    </div>
    <br/>
    <div class="md-form">
        <i class="fa fa-pencil prefix active"></i>
        <textarea type="text" class="md-textarea form-control" id="addmsg" name="addmsg" maxlength="50"
                  placeholder="">{!! $addl_text !!}</textarea>
        <label for="addmsg">Additional Message (Thanksgiving, etc. (opt.))</label>
    </div>
    <br/>
    <div class="md-form">
        <i class="fa fa-pencil prefix active"></i>
        <textarea type="text" class="md-textarea form-control" id="ecmsg" name="ecmsg" maxlength="50"
                  placeholder="">{!! $extended_text !!}</textarea>
        <label for="ecmsg">Extended Care Message (No Extended Care, etc. (opt.))</label>
    </div>
    <input type="hidden" id="ts" name="ts" value="{!! $thedate->getTimestamp() !!}" formnovalidate>
</div>