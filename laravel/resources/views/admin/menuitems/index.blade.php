@extends('layouts.app')
@section('title','Menu Items')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }

        #modalMenuItem .modal-dialog {
            margin: 120px auto 30px;
            width: 400px;
        }

        #tableMenuItems td:nth-child(2), #tableMenuItems td:nth-child(4) {
            text-align: right;
        }

        #tableMenuItems td:nth-child(3) {
            text-align: center;
        }

        #modalMenuItem #active {
            width: 100px;
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Menu Items</h3>
                <br/>
                <label for="selectprovider">Provider</label>
                <select class="custom-select" name="selectprovider" id="selectprovider">
                    <option value="0">- Select -</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->DT_RowId }}">{{ $provider->provider_name }}</option>
                    @endforeach
                </select>
                <div class="addeditdeletebuttons">
                    <button id="btnNew" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                class="fa fa-plus"></i> New
                    </button>
                    <div class="btn-group right-margin-1">
                        <button id="btnEdit" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-edit"></i> Edit
                        </button>
                        <button id="btnDel" type="button" class="btn btn-default btn-sm disabled" disabled><i
                                    class="fa fa-remove"></i> Del
                        </button>
                    </div>
                </div>
                <table id="tableMenuItems" class="table table-striped table-bordered table-hover editable table-sm">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Menu Item</th>
                        <th width="50">Price</th>
                        {{--<th width="75">Effective Date</th>--}}
                        <th width="50">Active</th>
                        <th width="70"># Orders</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalMenuItem" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header"></div>
                <form role="form" id="formMenuItem" autocomplete="off">
                    <div class="modal-body">
                        <div class="entityheader">
                            <label>Provider</label>
                            <div id="provider_name"></div>
                        </div>
                        <div class="msgAlert"></div>
                        <br/>

                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <textarea type="text" id="item_name" name="item_name" class="md-textarea form-control"
                                      maxlength="100" required rows="2"></textarea>
                            <label for="item_name">Menu Item Name</label>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-dollar prefix"></i>
                            <input type="number" min="0" max="10" step="0.01" data-number-to-fixed="2"
                                   data-number-stepfactor="100"
                                   class="form-control currency" name="price" id="price" required value="5.00"/>
                            <label for="price">Price</label>
                        </div>

                        <label for="active" style="margin-left:3rem">Active</label>
                        <div class="md-form">
                            <i class="fa fa-check prefix textRed"></i>
                            <select id="active" name="active" class="custom-select">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <input type="hidden" name="provider_id" value="0"/>
                        <input type="hidden" name="menuitem_id" value="0"/>

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
    <script src="{{ elixir('js/menuitems.js') }}"></script>
@endsection
