$(function () {
    'use strict';
    var USER_TYPE_STUDENT = 3,
        USER_TYPE_TEACHER = 4,
        DEFAULT_UNASSIGNED_ID = 1,
        ALLOWED_TO_ORDER_YES = 1,
        $msgAlert = $('.msgAlert'),
        $form = $('#formUser'),
        $accountName = $("#account_name"),

        $inpUserID = $("input[name='user_id']"),
        $inpAccountID = $("input[name='account_id']"),
        $inpFirstName = $("input[name='first_name']"),
        $inpLastName = $("input[name='last_name']"),

        $selTeacher = $("select[name='teacher_id']"),
        $selGrade = $("select[name='grade_id']"),
        $selAllowedToOrder = $("select[name='allowed_to_order']"),

        $fgGrades = $('#fg_grades'),
        $fgTeachers = $('#fg_teachers'),
        $fgAllowedToOrder = $('#fg_allowedtoorder'),

        $selUserType = $("select[name='user_type']").change(function (e) {
            doUserTypeDropdownChange($("option:selected", $selUserType).val())
        }),

        idToFind = 0,
        formInitialSerialized = '',
        needDataTableRefresh = false,
        selectedTeacherID = 0,
        selectedAccountID = 0,
        hrefequalpos = location.href.indexOf('='),

        $inputs = $('input').keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $btnSave.trigger('click')
            }
        }),

        $selAccounts = $("select[name='select_account']").change(function (e) {
            var a_id = $selAccounts.val();
            $inpAccountID.val(a_id);
            $accountName.text($("option:selected", $selAccounts).text());
            $dataTable.ajax.url('/admin/users/' + a_id).load();
            $.fn.handleButton($btnNew, a_id > 0, 'new');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
        }),

        $btnNew = $('#btnNew').click(function (e) {
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
            $inpUserID.val(0);
            $inpFirstName.val('');
            $inpLastName.val('');
            $selUserType.val(USER_TYPE_STUDENT);
            $selGrade.val(DEFAULT_UNASSIGNED_ID);
            $selTeacher.val(DEFAULT_UNASSIGNED_ID);
            $selAllowedToOrder.val(ALLOWED_TO_ORDER_YES);
            $fgGrades.show();
            $fgTeachers.show();
            $fgAllowedToOrder.hide();
            formInitialSerialized = 'new';
            $.fn.loadTeachers($selTeacher, function () {
                $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'User');
            })
        }),

        $btnEdit = $('#btnEdit').click(function (e) {
            formInitialSerialized = $form.serialize();
            $.fn.loadTeachers($selTeacher, function () {
                $selTeacher.val(selectedTeacherID);
                $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'User')
            })
        }),

        $btnDel = $('#btnDel').click(function (e) {
            var msg = '<br />You have selected to delete user:<br /><br />' +
                '<div class="textBlue">' + $inpLastName.val() + ', ' + $inpFirstName.val() + '</div>' +
                '<br /><b>Are you sure?</b><br /><br />';

            BootstrapDialog.mydelete(msg, function (result) {
                if (result) {
                    $.ajax({
                        type: 'DELETE',
                        data: {account_id: $inpAccountID.val()},
                        url: '/admin/users/' + $inpUserID.val()
                    }).done(function (data) {

                        if (data.error) {
                            BootstrapDialog.dberror(data.msg);
                        } else {
                            $dataTable.ajax.reload();
                            $.fn.handleButton($btnEdit, false);
                            $.fn.handleButton($btnDel, false);
                        }
                    })
                }
            })
        }),

        $modal = $('#modalUser').on('shown.bs.modal', function () {
            $inpLastName.focus();
            $inpFirstName.focus();
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

        $btnSave = $('#btnSave').click(function (e) {
            e.preventDefault();
            $inpFirstName.val($.trim($inpFirstName.val()));
            $inpLastName.val($.trim($inpLastName.val()));
            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide')
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/users',
                    data: formCurSerialized,
                    dataType: 'json'
                }).done(function (data) {
                    if (data.error) {
                        needDataTableRefresh = data.needrefresh;
                        $.fn.showModalFormError($msgAlert, data.msg);
                        $('.btn-default:first').focus()
                    } else {
                        idToFind = data.idToFind;
                        needDataTableRefresh = true;
                        $modal.modal('hide');
                    }
                })
            }
        }),

        $dataTable = $('#tableUsers').DataTable({
            dom: 'tip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            order: [[0, 'asc']],
            lengthMenu: [[15], [15]],
            columnDefs: [{
                targets: [6, 7, 8, 9, 10, 11, 2],
                visible: false
            }],
            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_ &nbsp;records per page'//,
                //emptyTable: '<i>No account selected</i>'
            }
        }).on('order.dt search.dt page.dt', function () {
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
        }).on('click', 'tbody tr', function () {
            clickRow($(this), 1)
        }).on('dblclick', 'tbody tr', function () {
            if (clickRow($(this), 2))
                $btnEdit.trigger('click')
        });

    function clickRow($this, clicks) {
        if ($dataTable.page.info().recordsTotal == 0)
            return false;

        if ($this.hasClass('selected') && clicks == 1) {
            $this.removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false)
        } else {
            $dataTable.$('tr.selected').removeClass('selected');
            $this.addClass('selected');
            $.fn.handleButton($btnEdit, true, 'edit');
            $.fn.handleButton($btnDel, true, 'delete');
            var selected_row_index = $dataTable.row($this).index();

            $inpUserID.val($dataTable.row($this).id());
            $inpLastName.val( $("<div/>").html( $dataTable.cell(selected_row_index, 0).data() ).text() );
            $inpFirstName.val( $("<div/>").html( $dataTable.cell(selected_row_index, 1).data() ).text() );
            $selUserType.val($dataTable.cell(selected_row_index, 6).data());
            $selGrade.val($dataTable.cell(selected_row_index, 7).data());
            selectedTeacherID = $dataTable.cell(selected_row_index, 8).data();
            $selAllowedToOrder.val($dataTable.cell(selected_row_index, 9).data());
            $inpAccountID.val($dataTable.cell(selected_row_index, 10).data());
            $accountName.text( $("<div/>").html( $dataTable.cell(selected_row_index, 11).data() ).text() );

            $fgAllowedToOrder.show();
            doUserTypeDropdownChange($dataTable.cell(selected_row_index, 6).data());
        }
        return true;
    }

    function doUserTypeDropdownChange(user_type) {
        if (user_type == USER_TYPE_STUDENT) {
            $fgGrades.show();
            $fgTeachers.show()
        } else if (user_type == USER_TYPE_TEACHER) {
            $fgGrades.show();
            $selTeacher.val(DEFAULT_UNASSIGNED_ID);
            $fgTeachers.hide()
        } else {
            $selGrade.val(DEFAULT_UNASSIGNED_ID);
            $selTeacher.val(DEFAULT_UNASSIGNED_ID);
            $fgGrades.hide();
            $fgTeachers.hide()
        }
    }

    if (hrefequalpos > 0) {
        var accid = location.href.substring(hrefequalpos + 1);
        if ($("option[value='" + accid + "']", $selAccounts).length > 0) {
            selectedAccountID = accid;
        }
    }

    $selAccounts.val(selectedAccountID).change();

});