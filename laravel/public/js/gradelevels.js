$(function () {
    'use strict';

    var $msgAlert = $('.msgAlert'),
        $form = $('#formGradeLevel'),
        $inpGradeID = $("input[name='grade_id']"),
        $inpGrade = $("input[name='grade']"),
        $inpGradeDesc = $("input[name='grade_desc']"),
        $inpReportOrder = $("input[name='report_order']"),
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
            $inpGradeID.val(0);
            $inpGrade.val('');
            $inpGradeDesc.val('');
            $inpReportOrder.val('');
            formInitialSerialized = 'new';
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'Grade Level');
        }),

        $btnEdit = $('#btnEdit').click(function (e) {
            formInitialSerialized = $form.serialize();
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'Grade Level');
        }),

        $btnDel = $('#btnDel').click(function (e) {
            BootstrapDialog.mydelete(
                '<br />You have selected to delete grade:<br /><br /><div class="textBlue">' + $inpGradeDesc.val() + '</div>' +
                '<br /><b>Are you sure?</b><br /><br />', function (result) {
                    if (result) {
                        $.ajax({
                            type: 'DELETE',
                            url: '/admin/gradelevels/' + $inpGradeID.val()
                        }).done(function (data) {
                            if (data.error) {
                                BootstrapDialog.dberror(data.msg);
                            } else {
                                $dataTable.ajax.reload();
                                $.fn.handleButton($btnEdit, false);
                                $.fn.handleButton($btnDel, false);
                            }
                        });
                    }
                })
        }),

        $btnSave = $('#btnSave').click(function (e) {
            $inpGrade.val($.trim($inpGrade.val()));
            $inpGradeDesc.val($.trim($inpGradeDesc.val()));

            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/gradelevels',
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

        $modal = $('#modalGradeLevel').on('shown.bs.modal', function () {
            $inpReportOrder.focus();
            $inpGradeDesc.focus();
            $inpGrade.focus();
        }).on('hide.bs.modal', function () {
            if (needDataTableRefresh) {
                needDataTableRefresh = false;

                $dataTable.ajax.reload(function (json) {
                    if (idToFind <= 0)
                        return;

                    var pos = $dataTable.order([2, 'asc']).row('#' + idToFind).index();
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

        $dataTable = $('#tableGradeLevels').DataTable({
            dom: 'tip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                type: 'GET',
                url: '/admin/gradelevels/0',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            order: [[2, 'asc']],
            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_&nbsp; records per page',
                emptyTable: 'No grade levels found'
            },
            lengthMenu: [[10], ['10']],
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
            $inpGradeID.val($dataTable.row($this).id());
            $inpGrade.val($("<div/>").html($dataTable.cell(selected_row_index, 0).data()).text());
            $inpGradeDesc.val( $("<div/>").html( $dataTable.cell(selected_row_index, 1).data() ).text() );
            $inpReportOrder.val($dataTable.cell(selected_row_index, 2).data());
        }
        return true;
    }
});