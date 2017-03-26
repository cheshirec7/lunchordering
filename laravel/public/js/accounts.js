$(function () {
    'use strict';
    var $msgAlert = $('.msgAlert'),
        $form = $('#formAccount'),
        $inpAccountID = $("input[name='account_id']"),
        $inpAccountName = $("input[name='account_name']"),
        $inpEmail = $("input[name='email']"),
        $inpFacebookUserID = $("input[name='fbuserid']"),
        idToFind = 0,
        formInitialSerialized = '',
        needDataTableRefresh = false,

        $inputs = $("input").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $btnSave.trigger('click');
            }
        }),

        $btnNew = $('#btnNew').click(function (e) {
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
            $.fn.handleButton($btnViewUsers, false);
            $inpAccountID.val(0);
            $inpAccountName.val('');
            $inpEmail.val('');
            $inpFacebookUserID.val('');
            formInitialSerialized = 'new';
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'Account')
        }),

        $btnEdit = $('#btnEdit').click(function (e) {
            formInitialSerialized = $form.serialize();
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'Account')
        }),

        $btnDel = $('#btnDel').click(function (e) {
            BootstrapDialog.mydelete('<br />You have selected to delete account:' +
                '<br /><br /><div class="textBlue">' + $inpAccountName.val() +
                '</div>' + '<br /><b>Are you sure?</b><br /><br />', function (result) {
                if (result) {
                    $.ajax({
                        type: 'DELETE',
                        url: '/admin/accounts/' + $inpAccountID.val()
                    }).done(function (data) {
                        if (data.error) {
                            BootstrapDialog.dberror(data.msg);
                        } else {
                            $dataTable.ajax.reload();
                            $.fn.handleButton($btnEdit, false);
                            $.fn.handleButton($btnDel, false);
                            $.fn.handleButton($btnViewUsers, false);
                        }
                    })
                }
            })
        }),

        $btnSave = $('#btnSave').click(function (e) {
            $inpEmail.val($.trim($inpEmail.val()));
            $inpAccountName.val($.trim($inpAccountName.val()));
            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/accounts',
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

        $modal = $('#modalAccount').on('shown.bs.modal', function () {
            $inpFacebookUserID.focus();
            $inpEmail.focus();
            $inpAccountName.focus();
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

        $btnViewUsers = $('#btnViewUsers').click(function (e) {
            window.location.href = "/admin/users/?aid="+$inpAccountID.val();
        }),

        $dataTable = $('#tableAccounts').DataTable({
            dom: 'ftip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                type: 'GET',
                url: '/admin/accounts/0',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            order: [[0, 'asc']],
            search: {"caseInsensitive": true},
            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_&nbsp; records per page',
                emptyTable: 'No accounts found'
            },
            lengthMenu: [[15], [15]],
            columnDefs: [{
                targets: [6],
                visible: false
            }],
            drawCallback: function () {
                $.fn.doPaginationAndLength(this);
            }
        }).on('order.dt search.dt page.dt', function () {
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
            $.fn.handleButton($btnViewUsers, false);
        }).on('click', 'tbody tr', function () {
            clickRow($(this), 1);
        }).on('dblclick', 'tbody tr', function () {
            if (clickRow($(this), 2))
                $btnEdit.trigger('click');
        });

    function clickRow($this, clicks) {
        if ($dataTable.page.info().recordsTotal == 0)
            return false;

        if ($this.hasClass('selected') && clicks == 1) {
            $this.removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
            $.fn.handleButton($btnViewUsers, false);
        } else {
            $dataTable.$('tr.selected').removeClass('selected');
            $this.addClass('selected');
            $.fn.handleButton($btnEdit, true, 'edit');
            $.fn.handleButton($btnDel, true, 'delete');
            $.fn.handleButton($btnViewUsers, true, 'entity');
            var selected_row_index = $dataTable.row($this).index();
            $inpAccountID.val($dataTable.row($this).id());
            $inpAccountName.val( $("<div/>").html( $dataTable.cell(selected_row_index, 0).data() ).text() );
            $inpEmail.val( $("<div/>").html( $dataTable.cell(selected_row_index, 1).data() ).text() );
            $inpFacebookUserID.val($dataTable.cell(selected_row_index, 6).data());
        }
        return true;
    }
});