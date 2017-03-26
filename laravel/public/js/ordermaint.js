$(function () {
    'use strict';

    var $msgAlert = $('.msgAlert'),
        $form = $('#formOrderMaint'),
        $table = $('#tableOrderMaint'),

        $inpOrderID = $("input[name='order_id']"),
        $orderDate = $('#order_date'),
        $inpOrderee = $("input[name='orderee']"),
        $inpOrderDesc = $("input[name='order_desc']"),
        $inpTotalPrice = $("input[name='total_price']"),
        $taNotes = $("textarea[name='notes']"),
        //$selTransferTo = $("select[name='transfer_to']"),

        $fgTotalPrice = $('#fg_total_price'),
        $fgTransferTo = $('#fg_transfer_to'),

        formInitialSerialized = '',
        needDataTableRefresh = false,
        idToFind = 0,
        selectedRow = {id: 0},

        $inputs = $("input").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $btnSave.trigger('click');
            }
        }),

        $selDates = $("select[name='select_date']").val(0).change(function (e) {
            var lunchdate_id = $selDates.val(),
                $selDate = $selDates.find(':selected'),
                locked = $selDate.data('locked'),
                numDays = $selDate.data('daysfromtoday');

            $dataTable.search('').columns().search(''); //TODO why the two searches??
            if (lunchdate_id == 0) {
                $dataTable.clear().draw();
                $switchLockUnlock.hide();
            } else {
                $dataTable.ajax.url('/admin/ordermaint/' + lunchdate_id).load(function (json) {
                    //var today = moment().startOf('day'),
                    //    seldate = moment(selected_date,'YYYY-MM-DD').startOf('day');
                    //if (today.diff(seldate,'days') <= 0) {
                    if (numDays >= 0) {
                        $switchLockUnlockInput.prop('checked', !locked);
                        $switchLockUnlock.show();
                    } else {
                        $switchLockUnlock.hide();
                    }
                });
            }
        }),

        $switchLockUnlock = $('#switchLockUnlock'),

        $switchLockUnlockInput = $('#switchLockUnlockInput').prop('checked', false).change(function (e) {
            var $seloption = $selDates.find(':selected'),
                lunchdate_id = $selDates.val(),
                thetext = $seloption.text();

            $.ajax({
                type: 'POST',
                url: '/admin/ordermaint/lunchdatelocktoggle/' + lunchdate_id,
                dataType: 'json'
            }).done(function (data) {
                if (!data.error) {
                    $switchLockUnlockInput.prop('checked', !data.locked);
                    if (data.locked) {
                        thetext = thetext.substr(0, thetext.indexOf('(') - 1);
                        $seloption.text(thetext);
                        $seloption.data('locked', 1);
                    } else {
                        $seloption.text(thetext + ' (Ordering Open)');
                        $seloption.data('locked', 0);
                    }
                    $dataTable.ajax.reload();
                }
            })
        }),


        $mnuEditOrder = $('#editOrder').click(function (e) {
            showForm('edit');
        }),
        $mnuTransferOrder = $('#transferOrder').click(function (e) {
            showForm('transfer');
        }),
        $mnuCancelOrder = $('#cancelOrder').click(function (e) {
            showForm('cancel');
        }),
        $mnuRestoreOrder = $('#restoreOrder').click(function (e) {
            showForm('restore');
        }),

        $btnOrderAction = $('#btnOrderAction'),

        $btnTransfer = $('#btnTransfer').click(function (e) {
            //BootstrapDialog.mydelete(
            //    '<br />You are about to delete a lunch order.<br /><br /><div class="textBlue">'+ $('option:selected',$selDates).text() + '<br />'+selectedRow.name + '<br />' +
            //    '</div>' +
            //    '<br /><b>Are you sure?</b><br /><br />', function(result) {
            //        if (result) {
            //            $.ajax({
            //                type: 'DELETE',
            //                url: '/admin/ordermaint/'+selectedRow.id
            //            }).done(function(data, textStatus, jqXHR) {
            //                //var o = JSON.parse(jqXHR.responseText);
            //                if (data.error) {
            //                    BootstrapDialog.dberror(data.msg);
            //                } else {
            //                    $dataTable.ajax.reload( function ( json ) {
            //                    } );
            //                }
            //            })
            //        }
            //    })
        }),

        $btnSave = $('#btnSave').click(function (e) {

            $taNotes.val($.trim($taNotes.val()));

            if ($inpTotalPrice.val() < 0) {
                $.fn.showModalFormError($msgAlert, 'The total price cannot be less than zero.');
                return;
            }

            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/ordermaint',
                    data: formCurSerialized,
                    dataType: 'json'
                }).done(function (data) {
                    if (data.error) {
                        needDataTableRefresh = data.needrefresh;
                        $.fn.showModalFormError($msgAlert, data.msg);
                        $('.btn-default:first').focus();
                    } else {
                        idToFind = data.idToFind;
                        needDataTableRefresh = true;
                        $modal.modal('hide');
                    }
                })
            }
        }),

        $modal = $('#modalOrderMaint').on('shown.bs.modal', function () {
            $taNotes.focus();
            $inpTotalPrice.focus();
        }).on('hide.bs.modal', function () {
            if (needDataTableRefresh) {
                needDataTableRefresh = false;
                $dataTable.ajax.reload(function (json) {
                    if (idToFind <= 0)
                        return;

                    var pos = $dataTable.order([0, 'asc']).row('#' + idToFind).index();
                    idToFind = 0;
                    if (pos >= 0) {
                        var page = Math.floor(pos / $dataTable.page.info().length);
                        $dataTable.page(page).draw(false).$('tbody > tr').each(function () {
                            var $this = $(this);
                            if (pos == $dataTable.row($this).index()) {
                                clickRow($this, 2);
                                return false;
                            }
                        });
                    }
                });
            }
        }),

        $dataTable = $table.DataTable({
            dom: 'ftip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                url: '/admin/ordermaint/0',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            order: [[0, 'asc']],
            columnDefs: [
                {
                    targets: [6],
                    visible: false
                },
                {
                    targets: [2, 3, 5],
                    searchable: false
                },
                {
                    targets: 4,
                    render: function (data, type, full, meta) {
                        if (data == 1)
                            return "<span class='locked'>-&nbsp;Locked&nbsp;-</span>";
                        else if (data == 2)
                            return "<span class='canceled'>-&nbsp;Canceled&nbsp;-</span>";
                        else if (data == 3)
                            return "<span class='transferred'>-&nbsp;Transferred&nbsp;-</span>";
                        else
                            return "";
                    }
                }
            ],

            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_ &nbsp;records per page',
                emptyTable: 'No date selected'
            },
            lengthMenu: [[15], [15]],
            drawCallback: function () {
                $.fn.doPaginationAndLength(this);
            }
        }).on('order.dt', function () {
            removeSelection();
        }).on('search.dt', function () {
            removeSelection();
        }).on('page.dt', function () {
            removeSelection();
        }).on('click', 'tbody tr', function () {
            clickRow($(this), 1);
        }).on('dblclick', 'tbody tr', function () {
            if (clickRow($(this), 2))
                $mnuEditOrder.trigger('click');
        });

    function removeSelection() {
        $dataTable.$('tr.selected').removeClass('selected');
        $.fn.handleButton($btnOrderAction, false);
    }

    function clickRow($this, clicks) {
        if ($dataTable.page.info().recordsTotal == 0)
            return false;

        if ($this.hasClass('selected') && clicks == 1) {
            $this.removeClass('selected');
            $.fn.handleButton($btnOrderAction, false);
        } else {
            $dataTable.$('tr.selected').removeClass('selected');
            $this.addClass('selected');
            $.fn.handleButton($btnOrderAction, true, 'primary');
            var selected_row_index = $dataTable.row($this).index();
            selectedRow.id = $dataTable.row($this).id();
            selectedRow.orderee = $dataTable.cell(selected_row_index, 0).data();
            selectedRow.total_price = $dataTable.cell(selected_row_index, 2).data().slice(1);
            selectedRow.status_code = $dataTable.cell(selected_row_index, 5).data();
            selectedRow.user_id = $dataTable.cell(selected_row_index, 6).data();

            selectedRow.order_desc = $("<div/>").html($dataTable.cell(selected_row_index, 1).data()).text();
            selectedRow.notes = $("<div/>").html($dataTable.cell(selected_row_index, 5).data()).text();
        }
        return true;
    }

    /////
    // function loadTransferToUser() {
    //     $.ajax({
    //         type: 'GET',
    //         url: '/admin/ordermaint/transfer',
    //         data: {user_id: selectedRow.user_id, lunchdate_id: $selDates.val()}
    //     }).done(function(arr) {
    //         var opts = '<option value="1">[ Select ]</option>';
    //         for (var row in arr) {
    //             opts += '<option value="'+arr[row].id+'">'+arr[row].name+'</option>';
    //         }
    //         $selTransferTo.html(opts);
    //         $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'Transfer');
    //     });
    // }

    /////
    function showForm(formType) {
        var $selDate = $selDates.find(':selected'),
            dateText = $selDate.text(),
            idxParen = dateText.indexOf('(');

        if (idxParen > 0)
            dateText = dateText.substr(0, idxParen - 2);
        $inpOrderID.val(selectedRow.id);
        $orderDate.val(dateText);
        $inpOrderee.val(selectedRow.orderee);
        $inpOrderDesc.val(selectedRow.order_desc);
        $inpTotalPrice.val(selectedRow.total_price);
        $taNotes.text(selectedRow.notes);
        formInitialSerialized = $form.serialize();

        if (formType == 'edit') {
            $fgTotalPrice.show();
            $fgTransferTo.hide();
            if (!selectedRow.notes)
            //$textareaNotes.attr('placeholder', 'Reason for price change, etc.');
                $taNotes.text('');
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'Order');
        } else if (formType == 'transfer') {
            // $fgTotalPrice.hide();
            // $fgTransferTo.show();
            // loadTransferToUser();
        } else if (formType == 'cancel') {

        } else if (formType == 'restore') {

        }
    }
});