$(function () {
    'use strict';

    var DEFAULT_UNASSIGNED_ID = 1,
        $msgAlert = $('.msgAlert'),
        $form = $('#formNLE'),
        $inpNLEID = $("input[name='nle_id']"),
        $inpExceptionDate = $("input[name='exception_date']"),
        $selGrade = $("select[name='grade_id']"),
        $selTeacher = $("select[name='teacher_id']"),
        $inpReason = $("input[name='reason']"),
        $inpDescription = $("textarea[name='description']").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $btnSave.trigger('click');
            }
        }),

        selectedRow = {},
        formInitialSerialized = '',
        needDataTableRefresh = false,
        idToFind = 0,

        $dateTimePicker = $('#datetimepicker1').datetimepicker({
            format: 'L',
            daysOfWeekDisabled: [0, 6],
            debug: false,
            useCurrent: false,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: "fa fa-chevron-left",
                next: "fa fa-chevron-right"
            }
        }),

        $inputs = $("input").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $btnSave.trigger('click');
            }
        }),

        $btnNew = $('#btnNew').click(function (e) {
            var theDate = moment().add(7, 'days');
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
            $inpNLEID.val(0);
            $selGrade.val(DEFAULT_UNASSIGNED_ID);
            $selTeacher.val(DEFAULT_UNASSIGNED_ID);
            $inpReason.val('Field Trip');
            $inpDescription.val('Please bring a sack lunch');
            $dateTimePicker.data('DateTimePicker').date(theDate).viewDate(theDate).minDate(moment()).maxDate(moment().add(300, 'days'));
            $radGrade.click();
            formInitialSerialized = 'new';
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'Exception');
        }),

        $btnEdit = $('#btnEdit').click(function (e) {
            $inpNLEID.val(selectedRow.id);
            $inpReason.val(selectedRow.reason);
            $inpDescription.val(selectedRow.description);
            $dateTimePicker.data('DateTimePicker').date(selectedRow.date).viewDate(selectedRow.date);
            if (selectedRow.teacher_id > DEFAULT_UNASSIGNED_ID)
                $radTeacher.click();
            else
                $radGrade.click();
            $selGrade.val(selectedRow.grade_id);
            $selTeacher.val(selectedRow.teacher_id);
            formInitialSerialized = $form.serialize();
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'Exception');
        }),

        $btnDel = $('#btnDel').click(function (e) {
            BootstrapDialog.mydelete(
                '<br />You have selected to delete exception:<br /><br />' +
                '<div class="textBlue">' +
                selectedRow.date.format("dddd, MMMM D, YYYY") + '<br />' +
                selectedRow.teacher_grade + '<br />' +
                selectedRow.reason + '<br />' +
                '</div>' +
                '<br /><b>Are you sure?</b><br /><br />', function (result) {
                    if (result) {
                        $.ajax({
                            type: 'DELETE',
                            url: '/admin/nolunchexceptions/' + selectedRow.id
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

            var grade_id = $selGrade.val(),
                teacher_id = $selTeacher.val();

            $inpExceptionDate.val($.trim($inpExceptionDate.val()));
            $inpReason.val($.trim($inpReason.val()));
            $inpDescription.val($.trim($inpDescription.val()));

            if (grade_id <= DEFAULT_UNASSIGNED_ID && teacher_id <= DEFAULT_UNASSIGNED_ID) {
                $.fn.showModalFormError($msgAlert, 'Please select a Teacher or a Grade.');
                return;
            }

            if ($inpExceptionDate.val() == '') {
                $dateTimePicker.data('DateTimePicker').date(moment());
            }

            var inpDate = moment($inpExceptionDate.val(), 'MM/DD/YYYY'),
                nextYear = moment().add(1, 'years'),
                today = moment();

            if (inpDate.isBefore(today)) {
                $.fn.showModalFormError($msgAlert, 'Date must be later than today.');
                return;
            }

            if (inpDate.isAfter(nextYear)) {
                $.fn.showModalFormError($msgAlert, 'Date is too far in the future.');
                return;
            }

            if (inpDate.weekday() == 0 || inpDate.weekday() == 6) {
                $.fn.showModalFormError($msgAlert, 'Date must be a weekday.');
                return;
            }

            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/nolunchexceptions',
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

        $modal = $('#modalNLE').on('shown.bs.modal', function () {
            $inpDescription.focus();
            $inpReason.focus();
            $inpExceptionDate.focus();
        }).on('hide.bs.modal', function () {
            if (needDataTableRefresh) {
                needDataTableRefresh = false;

                $dataTable.ajax.reload(function (json) {
                    if (idToFind <= 0)
                        return;

                    var pos = $dataTable.order([0, 'desc']).row('#' + idToFind).index();
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

        $radGrade = $("#radGrade").click(function (e) {
            $selGrade.val(DEFAULT_UNASSIGNED_ID).show();
            $selTeacher.val(DEFAULT_UNASSIGNED_ID).hide();
        }),

        $radTeacher = $("#radTeacher").click(function (e) {
            $selGrade.val(DEFAULT_UNASSIGNED_ID).hide();
            $selTeacher.val(DEFAULT_UNASSIGNED_ID).show();
        }),

        $dataTable = $('#tableNLE').DataTable({
            dom: 'tip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                type: 'GET',
                url: '/admin/nolunchexceptions/0',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            columnDefs: [{
                targets: [4, 5],
                visible: false
            }],
            order: [[0, 'desc']],
            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_ &nbsp;records per page',
                emptyTable: 'No exceptions found'
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
            selectedRow.id = $dataTable.row($this).id();
            selectedRow.date = moment($dataTable.cell(selected_row_index, 0).data());
            selectedRow.teacher_grade = $dataTable.cell(selected_row_index, 1).data();
            selectedRow.reason = $("<div/>").html($dataTable.cell(selected_row_index, 2).data()).text();
            selectedRow.description = $("<div/>").html($dataTable.cell(selected_row_index, 3).data()).text();
            selectedRow.teacher_id = +($dataTable.cell(selected_row_index, 4).data());
            selectedRow.grade_id = +($dataTable.cell(selected_row_index, 5).data());
        }
        return true;
    }
});