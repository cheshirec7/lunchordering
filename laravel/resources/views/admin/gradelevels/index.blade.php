@extends('layouts.app')
@section('title', 'Grade Levels')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }
        #modalGradeLevel .modal-dialog {
            margin: 120px auto 30px;
            width: 300px;
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Grade Levels</h3>
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
                <table id="tableGradeLevels" class="table table-striped table-bordered table-hover table-sm editable">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Grade</th>
                        <th>Description</th>
                        <th width="75">Report Order</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="modalGradeLevel" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form role="form" id="formGradeLevel" autocomplete="off">
                    <div class="modal-header"></div>
                    <div class="modal-body">
                        <div class="msgAlert"></div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="grade" name="grade" maxlength="10" required/>
                            <label for="grade">Grade</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="grade_desc" name="grade_desc" maxlength="50"
                                   required/>
                            <label for="grade_desc">Description</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="number" class="form-control" id="report_order" name="report_order" min="1"
                                   max="999" required/>
                            <label for="report_order">Report Order</label>
                        </div>
                        <input type="hidden" name="grade_id" value="0"/>
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
    <script src="{{ elixir('js/gradelevels.js') }}"></script>
@endsection
