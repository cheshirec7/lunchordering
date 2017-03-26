@extends('layouts.app')
@section('title','Schedule Lunches')
@section('styles')
    <link href="{{ elixir('css/schedule.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div id="spinner"></div>

    <div id="schedlunchestext">[Schedule Lunches]</div>
    <div class="schednav">
        <div class="navbtn prev"></div>
        <div id="schedmonthyear">{!! date('F Y') !!}</div>
        <div class="navbtn next"></div>
    </div>

    <div id="caroSchedule" class="carousel slide" data-ride="carousel" data-wrap="false" data-interval="false">
        <div class="carousel-inner" role="listbox">
            <div class="carousel-item active">
                {!! $thetable !!}
            </div>
        </div>
    </div>

    <div id="modalScheduleDay" class="modal fade" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="scheduledayform" name="scheduledayform">
                    <div class="modal-header">
                        <div id="modaltitle">Lunch Scheduling</div>
                        <div id="scheduledate"></div>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="margin0">Provider</label>
                            <select id="select_provider" style="width:100%" name="provider_id" class="custom-select">
                                <option value="0">[No Provider Selected]</option>
                                <option value="1">No Lunch (No School)</option>
                                <option value="2">No Lunch (Early Dismissal)</option>
                                <option value="3">Lunch Provided</option>
                                @foreach($providers as $provider)
                                    <option value="{!! $provider->id !!}">{!! $provider->provider_name !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="menuitemscontainer">
                            <label class="margin0">Lunches Available</label>
                            <div class="form-group">
                                <div id="scrollbox"></div>
                            </div>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix active"></i>
                            {{--<textarea type="text" class="md-textarea form-control" id="addmsg" name="addmsg" maxlength="50"></textarea>--}}
                            <input type="text" class="form-control" id="addmsg" name="addmsg" maxlength="50"></input>
                            <label for="addmsg">Additional Message (Thanksgiving, etc.)</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix active"></i>
                            {{--<textarea type="text" class="md-textarea form-control" id="ecmsg" name="ecmsg" maxlength="50"></textarea>--}}
                            <input type="text" class="form-control" id="ecmsg" name="ecmsg" maxlength="50"></input>
                            <label for="ecmsg">Extended Care Message (No Extended Care, etc.)</label>
                        </div>
                        <input type="hidden" id="ts" name="ts">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel</button>
                        <button id="btnSave" type="submit" class="btn btn-primary btn-sm">&nbsp;Save&nbsp;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/schedule.js') }}"></script>
@endsection


