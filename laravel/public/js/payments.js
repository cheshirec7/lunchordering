$(function () {
    'use strict';

    var $msgAlert = $('.msgAlert'),
        $form = $('#formPayment'),
        $paymentBalanceText = $('#paymentBalanceText').hide(),
        $accountName = $("#account_name"),
        $inpAccountID = $("input[name='account_id']"),
        $inpPayID = $("input[name='pay_id']"),
        $selPayMethod = $("select[name='pay_method']"),
        $inpCreditDesc = $("input[name='credit_desc']"),
        $inpCreditAmt = $("input[name='credit_amt']"),
        $inpCreditDate = $("input[name='credit_date']"),

        $dateTimePicker = $('#datetimepicker1').datetimepicker({
            format: 'L'//,
            //debug: true
        }),

        idToFind = 0,
        formInitialSerialized = '',
        needDataTableRefresh = false,
        selectedRow = {id: 0},

        $inputs = $("input").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $btnSave.trigger('click');
            }
        }),

        $selAccounts = $("select[name='select_account']").val(0).change(function (e) {
            var a_id = $selAccounts.val();
            $inpAccountID.val(a_id);
            $accountName.text($("option:selected", $selAccounts).text());

            $.fn.handleButton($btnNew, a_id > 0, 'new');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);

            if (a_id == 0) {
                $dataTable.clear().draw();
                $paymentBalanceText.text('').hide();
            } else {
                $dataTable.ajax.url('/admin/payments/' + a_id).load(function (json) {
                    updateBalance();
                });
            }
        }),

        $btnNew = $('#btnNew').click(function (e) {
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
            $inpPayID.val(0);
            $selPayMethod.val(0);
            $inpCreditAmt.val('0.00');
            $dateTimePicker.data("DateTimePicker").date(moment()).minDate(moment().subtract(300, 'days')).maxDate(moment().add(300, 'days'));
            $inpCreditDesc.val('');
            formInitialSerialized = 'new';
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'Payment');
        }),

        $btnEdit = $('#btnEdit').click(function (e) {
            $inpPayID.val(selectedRow.id);
            $selPayMethod.val(selectedRow.pay_method_int);
            $inpCreditAmt.val(selectedRow.credit_amt);
            $dateTimePicker.data("DateTimePicker").date(selectedRow.credit_date).viewDate(selectedRow.credit_date).minDate(moment().subtract(300, 'days')).maxDate(moment().add(300, 'days'));
            $inpCreditDesc.val(selectedRow.credit_desc);

            formInitialSerialized = $form.serialize();
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'Payment');
        }),

        $btnDel = $('#btnDel').click(function (e) {
            BootstrapDialog.mydelete(
                '<br />You are about to delete a payment.<br /><br /><div class="textBlue">' +
                'Account: <br />' + $('option:selected', $selAccounts).text() + '<br /><br />' +
                'Received: <br />' + selectedRow.credit_date.format("dddd, MMMM D, YYYY") + '<br /><br />' +
                'Amount: <br />$' + selectedRow.credit_amt +
                '</div>' +
                '<br /><b>Are you sure?</b><br /><br />', function (result) {
                    if (result) {
                        $.ajax({
                            type: 'DELETE',
                            data: {account_id: $inpAccountID.val()},
                            url: '/admin/payments/' + selectedRow.id
                        }).done(function (data) {
                            $.fn.handleButton($btnEdit, false);
                            $.fn.handleButton($btnDel, false);
                            if (data.error) {
                                BootstrapDialog.dberror(data.msg);
                            } else {
                                $dataTable.ajax.reload(function (json) {
                                    updateBalance();
                                });
                            }
                        })
                    }
                })
        }),

        $btnSave = $('#btnSave').click(function (e) {

            $inpCreditDesc.val($.trim($inpCreditDesc.val()));
            if ($inpCreditDate.val() == '') {
                $dateTimePicker.data('DateTimePicker').date(moment());
            }

            if ($selPayMethod.val() == 0) {
                $.fn.showModalFormError($msgAlert, 'Please select a payment method.');
                return;
            }

            if ($inpCreditAmt.val() == 0) {
                $.fn.showModalFormError($msgAlert, 'The amount cannot be zero.');
                return;
            }

            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/payments',
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

        $modal = $('#modalPayment').on('shown.bs.modal', function () {
            $inpCreditAmt.focus();
            $inpCreditDesc.focus();
            $inpCreditDate.focus();
        }).on('hide.bs.modal', function () {
            if (needDataTableRefresh) {
                needDataTableRefresh = false;
                $dataTable.ajax.reload(function (json) {
                    updateBalance();
                    if (idToFind <= 0)
                        return;

                    var pos = $dataTable.order([3, 'asc']).row('#' + idToFind).index();
                    idToFind = '';
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

        $dataTable = $('#tablePayments').DataTable({
            dom: 't',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                url: '/admin/payments/0',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            columnDefs: [{
                targets: [4],
                visible: false
            }],
            order: [[3, 'asc']],
            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_ &nbsp;records per page',
                emptyTable: '<i>No payments</i>'
            },
            lengthMenu: [[-1], ["All"]],
            drawCallback: function () {
                $.fn.doPaginationAndLength(this);
            }
        }).on('order.dt search.dt page.dt', function () {
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
        }).on('click', 'tbody tr', function () {
            clickRow($(this), 1);
        }).on('dblclick', 'tbody tr', function () {
            if (clickRow($(this), 2))
                $btnEdit.trigger('click');
        });

    function updateBalance(balance) {
        $.ajax({
            type: 'GET',
            url: '/admin/accounts/balance/' + $inpAccountID.val()
        }).done(function (data) {

            var tfoot = '';
            if (data.bal == 0) {
                tfoot += 'Current Account Balance&nbsp;&nbsp;<div>$0.00</div>';
            } else if (data.bal < 0) {
                tfoot += 'Balance Due&nbsp;&nbsp;';
                tfoot += '<div class="balancedue">$' + Math.abs(data.bal / 100).toFixed(2) + '</div>';
            } else {
                tfoot += 'Credit Available&nbsp;&nbsp;';
                tfoot += '<div class="creditavailable">$' + Math.abs(data.bal / 100).toFixed(2) + '</div>';
            }
            $paymentBalanceText.html(tfoot).show();
        });
    }

    function clickRow($this, clicks) {
        if ($dataTable.page.info().recordsTotal == 0)
            return false;

        if ($this.hasClass('selected') && clicks == 1) {
            $this.removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
        } else {
            $dataTable.$('tr.selected').removeClass('selected');
            $this.addClass('selected');
            $.fn.handleButton($btnEdit, true, 'edit');
            $.fn.handleButton($btnDel, true, 'delete');
            var selected_row_index = $dataTable.row($this).index();
            selectedRow.id = $dataTable.row($this).id();
            selectedRow.credit_desc = $("<div/>").html($dataTable.cell(selected_row_index, 1).data()).text();
            selectedRow.credit_amt = $dataTable.cell(selected_row_index, 2).data().slice(1);
            selectedRow.credit_date = moment($dataTable.cell(selected_row_index, 3).data());
            selectedRow.pay_method_int = $dataTable.cell(selected_row_index, 4).data();
        }
        return true;
    }
});