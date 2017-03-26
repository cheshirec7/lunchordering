@extends('layouts.app')
@section('title','Order Maintenance')
@section('styles')
    <style>
        body {
            background-color: #fafafa;
        }

        .myheader {
            width: 100%;
            background-color: #eee;
            padding: 10px;
            margin: 10px 0 25px;
            border-radius: 2px;
            border: 1px solid #ddd
        }

        #tableOrderMaint td:nth-child(5) {
            text-align: center;
        }

        #tableOrderMaint td:nth-child(3) {
            text-align: right;
            padding-right: 8px;
        }

        #tableOrderMaint .scheduled {
            color: #008800;
        }

        #tableOrderMaint .locked {
            color: #EB9316;
        }

        #tableOrderMaint .canceled {
            color: #c12e2a;
        }

        #tableOrderMaint .transferred {
            color: #265a88;
        }

        #tableOrderMaint th:nth-child(7) {
            padding-right: 4px !important;
        }

        #tableOrderMaint_filter input {
            width: 300px;
            padding: 0;
            margin: 0 0 6px 10px;
        }

        #modalOrderMaint .modal-dialog {
            margin: 120px auto 30px;
            width: 400px;
        }
    </style>
@endsection
@section('content')
    <div id="spinner"></div>

    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-block">
                <h3><i class="fa fa-th"></i> Order Maintenance</h3>
                <div class="myheader">
                    <label for="select_date">Date&nbsp;&nbsp;</label>
                    <select class="custom-select" name="select_date" id="select_date">
                        <option value="0">- Select Date -</option>
                        @foreach ($dates as $date)
                            <?php
                            $dtDate = new DateTime($date->provide_date);
                            $lockedVal = 0;
                            $orderingAvailableText = ' (Ordering Open)';
                            if (!is_null($date->orders_placed)) {
                                $lockedVal = 1;
                                $orderingAvailableText = '';
                            }
                            ?>
                            <option data-locked={{ $lockedVal }} data-daysfromtoday={{ $date->daysfromtoday }} value="{{ $date->lunchdate_id }}">{{ $dtDate->format('l, F jS, Y') }}{{ $orderingAvailableText }}</option>
                        @endforeach
                    </select>
                    <span id="switchLockUnlock" class="switch" style="display: none"><label>&nbsp;&nbsp;Locked<input
                                    id="switchLockUnlockInput" type="checkbox"><span class="lever"></span>Open (Allow New Orders)</label></span>
                </div>

                <div class="addeditdeletebuttons">
                    <div class="btn-group">
                        <button id="btnOrderAction" class="btn btn-default btn-sm dropdown-toggle" disabled
                                type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Order Actions
                        </button>
                        <div class="dropdown-menu">
                            <a id="editOrder" class="dropdown-item" href="#">Edit</a>
                            {{--<a class="dropdown-item" href="#">Transfer</a>--}}
                            {{--<a class="dropdown-item" href="#">Cancel</a>--}}
                            {{--<a class="dropdown-item" href="#">Restore</a>--}}
                        </div>
                    </div>
                </div>

                <table id="tableOrderMaint" class="table table-striped table-bordered table-hover table-sm editable">
                    <thead class="thead-inverse">
                    <tr>
                        <th>Ordered By</th>
                        <th>Order Description</th>
                        <th width="1">Price&nbsp;</th>
                        <th>Grade</th>
                        <th width="1">Status&nbsp;</th>
                        <th>Notes</th>
                        <th class="hidden">user_id</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalOrderMaint" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header"></div>
                <form role="form" id="formOrderMaint" autocomplete="off">
                    <div class="modal-body">
                        <div class="msgAlert"></div>
                        <br/>
                        <div class="md-form">
                            <label for="order_date" class="margin0">Date</label>
                            <input id="order_date" name="order_date" type="text" disabled placeholder="">
                        </div>

                        <div class="md-form">
                            <label for="orderee" class="margin0">Ordered By</label>
                            <input type="text" id="orderee" name="orderee" disabled placeholder=""/>
                        </div>

                        <div class="md-form">
                            <label for="order_desc">Description</label>
                            <input type="text" id="order_desc" name="order_desc" disabled placeholder=""/>
                        </div>

                        <br/>
                        <div id="fg_total_price" class="form-group">
                            <div class="md-form">
                                <i class="fa fa-dollar prefix"></i>
                                <input type="number" min="0" max="10" step="0.01" data-number-to-fixed="2"
                                       data-number-stepfactor="100" placeholder=""
                                       class="form-control currency" name="total_price" id="total_price" required/>
                                <label for="total_price">Price</label>
                            </div>
                        </div>

                        <div id="fg_transfer_to" class="form-group">
                            <label for="transfer_to">Transfer To</label>
                            <select id="transfer_to" name="transfer_to" class="form-control">
                            </select>
                        </div>
                        <br/>
                        <div class="md-form">
                            <i class="fa fa-pencil prefix"></i>
                            <textarea type="text" id="notes" name="notes" class="md-textarea form-control" required
                                      placeholder=""></textarea>
                            <label for="notes">Admin Notes (Reason for price change, etc.)</label>
                        </div>

                        <input type="hidden" name="order_id" value="0"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel
                        </button>
                        <button id="btnSave" type="button" class="btn btn-sm btn-primary">&nbsp;Save&nbsp;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ elixir('js/ordermaint.js') }}"></script>
    {{--<script src="/js/ordermaint.js"></script>--}}
@endsection
