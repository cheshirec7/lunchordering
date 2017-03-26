$(document).ready(function () {
    "use strict";

    var $loading = $('.loader').hide(),
        $document = $(document).ajaxStart(function () {
            $loading.show();
        }).ajaxError(function (event, jqxhr, settings, thrownError) {
            $loading.hide();
            if (jqxhr.status == 401) //unauthorized
                location.reload();
            else
                location.reload();
        }).ajaxStop(function () {
            $loading.hide();
        });

    /////
    $.ajaxSetup({
        cache: false,
        headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken
        }
    });

    /////
    BootstrapDialog.mydelete = function (message, callback) {
        return new BootstrapDialog({
            title: 'Delete Confirmation',
            cssClass: 'modaldelete',
            message: message,
            type: BootstrapDialog.TYPE_DANGER,
            closable: false,
            data: {
                'callback': callback
            },
            buttons: [{
                label: 'Cancel',
                cssClass: 'btn-default btn-sm',
                action: function (dialog) {
                    typeof dialog.getData('callback') === 'function' && dialog.getData('callback')(false);
                    dialog.close();
                }
            }, {
                label: 'Yes, Delete',
                cssClass: 'btn-danger btn-sm',
                action: function (dialog) {
                    typeof dialog.getData('callback') === 'function' && dialog.getData('callback')(true);
                    dialog.close();
                }
            }]
        }).open();
    };

    /////
    BootstrapDialog.dberror = function (message) {
        return new BootstrapDialog({
            title: 'Information',
            cssClass: 'modaldelete',
            message: message,
            type: BootstrapDialog.TYPE_WARNING,
            closable: true,
            buttons: [{
                label: 'OK',
                cssClass: 'btn-default btn-sm float-xs-right',
                action: function (dialog) {
                    dialog.close();
                }
            }]
        }).open();
    };

    /////
    $.fn.dataTable.ext.errMode = 'throw';

    /////
    $.fn.dataTableExt.oApi.clearSearch = function (oSettings) {
        var table = this,
            // clearSearch = $('<button class="btn btn-default btn-sm" style="margin:0;" type="button" title="Clear"><i class="fa fa-times-circle-o"></i></button>');
            clearSearch = $('<button class="btn btn-search btn-sm" type="button" title="Clear">x</button>');
        $(clearSearch).click(function () {
            table.fnFilter('');
            $('input[type=search]').val('');
        });
        $(oSettings.nTableWrapper).find('div.dataTables_filter label').append(clearSearch);
    };

    /////
    $.fn.dataTable.models.oSettings['aoInitComplete'].push({
        fn: $.fn.dataTableExt.oApi.clearSearch,
        sName: 'myClearSearch'
    });

    /////
    $.fn.dataTable.Api.register('page.jumpToData()', function (data, column) {
        var pos = this.column(column, {order: 'current'}).data().indexOf(data);
        if (pos >= 0) {
            var page = Math.floor(pos / this.page.info().length);
            this.page(page).draw(false);
        }
        return this;
    });

    /////
    $.fn.doPaginationAndLength = function (o) {
        var wrapper = o.parent(),
            rowsPerPage = o.fnSettings()._iDisplayLength,
            rowsToShow = o.fnSettings().fnRecordsDisplay(),
            minRowsPerPage = o.fnSettings().aLengthMenu[0][0];

        if (rowsToShow <= rowsPerPage || rowsPerPage == -1) {
            $('.dataTables_paginate', wrapper).css('display', 'none');
        } else {
            $('.dataTables_paginate', wrapper).css('display', 'inline');
        }
        if (rowsToShow <= minRowsPerPage) {
            $('.dataTables_length', wrapper).css('display', 'none');
        } else {
            $('.dataTables_length', wrapper).css('display', 'inline');
        }
    };

    /////
    $.fn.showModalFormError = function ($selector, msg) {
        var s = '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' +
            '<div>' + msg + '</div></div>';
        $selector.html(s);
    };

    $.fn.handleButton = function ($btn, enable, theclass) {
        if (enable)
            $btn.prop('disabled', false).removeClass('btn-default btn-primary btn-success btn-warning btn-new btn-edit btn-delete btn-entity disabled').addClass('btn-' + theclass);
        else
            $btn.prop('disabled', true).removeClass('btn-primary btn-success btn-warning btn-new btn-edit btn-delete btn-entity').addClass('btn-default disabled');
    };

    /////
    $.fn.showModalForm = function ($modal, $btnSave, $msgAlert, type, title) {
        $('.modal-header', $modal).html(type + ' ' + title);
        if (type === 'Edit') {
            $modal.removeClass('new').addClass('edit');
            $btnSave.removeClass('btn-new').addClass('btn-edit');
        } else {
            $modal.removeClass('edit').addClass('new');
            $btnSave.removeClass('btn-edit').addClass('btn-new');
        }

        if ($msgAlert)
            $msgAlert.html('');
        $modal.modal();
    };

    /////
    $.fn.loadTeachers = function ($selTeacher, cb) {
        $.ajax({
            type: 'GET',
            url: '/admin/users/teachers'
        }).done(function (arrTeachers) {
            var opts = '<option value="1">[ Select ]</option>';
            for (var row in arrTeachers) {
                var obj = arrTeachers[row];
                var name = obj.last_name + ', ' + obj.first_name;
                if (obj.grade_desc !== '(unassigned)')
                    name += ' (' + obj.grade_desc + ')';
                opts += '<option value="' + obj.id + '">' + name + '</option>';
            }
            $selTeacher.html(opts);
            cb();
        });
    };
});