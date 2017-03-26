@extends('layouts.app')
@section('title','Providers')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }

        #modalProvider .modal-dialog {
            margin: 120px auto 30px;
            width: 400px;
        }

        #tableProviders td:nth-child(5) {
            text-align: center;
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Providers</h3>
                <div class="addeditdeletebuttons">
                    <button id="btnNew" type="button" class="btn btn-new btn-sm"><i class="fa fa-plus"></i>&nbsp;New
                    </button>
                    <div class="btn-group">
                        <button id="btnEdit" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-edit"></i>&nbsp;Edit
                        </button>
                        <button id="btnDel" type="button" class="btn btn-default btn-sm btn-sm disabled" disabled><i
                                    class="fa fa-remove"></i>&nbsp;Del
                        </button>
                    </div>
                </div>
                <table id="tableProviders" class="table table-striped table-bordered table-hover editable table-sm">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Name</th>
                        <th>Image</th>
                        <th>URL</th>
                        <th>Included With Lunches Message</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="modalProvider" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header"></div>
                <form role="form" id="formProvider" autocomplete="off">
                    <div class="modal-body">
                        <div class="msgAlert"></div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" id="provider_name" name="provider_name"
                                   maxlength="50" required="required"/>
                            <label for="provider_name">Provider Name</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <input type="text" class="form-control" name="provider_url" id="provider_url"
                                   maxlength="255" required="required"/>
                            <label for="provider_url">URL</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <textarea type="text" id="provider_includes" name="provider_includes"
                                      class="md-textarea form-control" required="required"></textarea>
                            <label for="provider_includes">Included With Lunches Message</label>
                        </div>

                        <div style="padding-left:3em;">
                            <label for="provider_image">Image <i>(must scale to 115x66px)</i></label>
                            <div class="md-form">
                                <select class="custom-select" name="provider_image" id="provider_image">
                                    @foreach ($files as $index=>$file)
                                        <option value="{{ $file }}">{{ $file }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="provider_id" value="0"/>
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
    <script src="{{ elixir('js/providers.js') }}"></script>
@endsection
