@extends('layouts.app')
@section('title','No Lunch Exceptions')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }

        #modalNLE .modal-dialog {
            margin: 120px auto 30px;
            width: 350px;
        }

        .container.page.nle {
            min-height: 500px;
            max-width: 800px;
        }

        .mydate i {
            top: 22px !important;
            right: 40px;
            display: none !important;
        }

        .exception_for {
            margin-bottom: 1px;
            color: #999;
            font-style: italic;
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> No Lunch Exceptions</h3>
                <div class="addeditdeletebuttons">
                    <button id="btnNew" type="button" class="btn btn-new btn-sm"><i class="fa fa-plus"></i> New
                    </button>
                    <div class="btn-group">
                        <button id="btnEdit" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-edit"></i> Edit
                        </button>
                        <button id="btnDel" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-remove"></i> Del
                        </button>
                    </div>
                </div>
                <table id="tableNLE" class="table table-striped table-bordered table-hover table-sm editable">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Date</th>
                        <th>Teacher / Grade</th>
                        <th>Reason</th>
                        <th>Description</th>
                        <th class="hidden">gid</th>
                        <th class="hidden">tid</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="modalNLE" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form role="form" id="formNLE" autocomplete="off" class="form-inline1">
                    <div class="modal-header"></div>
                    <div class="modal-body">
                        <div class="msgAlert"></div>

                        <label for="exception_date">Exception Date</label>
                        <div class="md-form">
                            <div class="input-group date" id="datetimepicker1">
                                <input type="text" class="form-control" id="exception_date" name="exception_date"/>
                                <span class="input-group-addon"><i class="fa fa-calendar-o"></i></span>
                            </div>
                        </div>
                        <br/>

                        <input name="exception_type" type="radio" id="radGrade" value="2" checked>
                        <label for="radGrade" class="margin0">Grade Exception&nbsp;&nbsp;</label>
                        <input name="exception_type" type="radio" id="radTeacher" value="1">
                        <label for="radTeacher" class="margin0">Teacher Exception</label>

                        <div class="md-form">
                            <select id="teacher_id" name="teacher_id" class="custom-select" style="width: 100%">
                                <option value="1">[ Select ]</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->teacher_name }}</option>
                                @endforeach
                            </select>
                            <select id="grade_id" name="grade_id" class="custom-select" style="width: 100%">
                                <option value="1">[ Select ]</option>
                                @foreach ($gradelevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->grade_desc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="reason" name="reason" maxlength="30"
                                   required="required"/>
                            <label for="reason">Reason</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <textarea type="text" id="description" name="description" class="md-textarea form-control"
                                      required="required" maxlength="50"></textarea>
                            <label for="description">Description</label>
                        </div>

                        <input type="hidden" name="nle_id" value="0"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel
                        </button>
                        <button id="btnSave" type="button" class="btn btn-primary btn-sm">&nbsp;Save&nbsp;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/nolunchexceptions.js') }}"></script>
    {{--<script src="/js/nolunchexceptions.js"></script>--}}
@endsection
