$(function () {
    'use strict';

    var $msgAlert = $('.msgAlert'),
        $form = $('#formProvider'),
        $inpProviderID = $("input[name='provider_id']"),
        $inpProviderName = $("input[name='provider_name']"),
        $selProviderImage = $("select[name='provider_image']"),
        $inpProviderURL = $("input[name='provider_url']"),
        idToFind = 0,
        formInitialSerialized = '',
        needDataTableRefresh = false,

        $inpProviderIncludes = $("textarea[name='provider_includes']").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $btnSave.trigger('click');
            }
        }),

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
            $inpProviderID.val(0);
            $inpProviderName.val('');
            $inpProviderURL.val('');
            $inpProviderIncludes.val('');
            $selProviderImage.prop('selectedIndex', 0);
            formInitialSerialized = 'new';
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'Provider');
        }),

        $btnEdit = $('#btnEdit').click(function (e) {
            formInitialSerialized = $form.serialize();
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'Provider');
        }),

        $btnDel = $('#btnDel').click(function (e) {
            BootstrapDialog.mydelete(
                '<br />You have selected to delete provider:<br /><br /><div class="textBlue">' + $inpProviderName.val() + '</div>' +
                //'<br />You will only be able to do this if there are no menu items attached to orders for this provider.<br />'+
                '<br /><b>Are you sure?</b><br /><br />', function (result) {
                    if (result) {
                        $.ajax({
                            type: 'DELETE',
                            url: '/admin/providers/' + $inpProviderID.val()
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

        $modal = $('#modalProvider').on('shown.bs.modal', function () {
            $inpProviderIncludes.focus();
            $inpProviderURL.focus();
            $inpProviderName.focus();
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

            $inpProviderName.val($.trim($inpProviderName.val()));
            $inpProviderURL.val($.trim($inpProviderURL.val()));
            $inpProviderIncludes.val($.trim($inpProviderIncludes.val()));

            var formCurSerialized = $form.serialize();

            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/providers',
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

        $dataTable = $('#tableProviders').DataTable({
            dom: 'tip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                url: '/admin/providers/0',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            order: [[0, 'asc']],
            language: {
                search: 'Search',
                emptyTable: 'No providers found'
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

            if ($dataTable.page.info().recordsTotal == 0)
                return;

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
                $inpProviderID.val($dataTable.row($this).id());
                $selProviderImage.val($dataTable.cell(selected_row_index, 1).data());
                $inpProviderName.val($("<div/>").html($dataTable.cell(selected_row_index, 0).data()).text());
                $inpProviderURL.val($("<div/>").html($dataTable.cell(selected_row_index, 2).data()).text());
                $inpProviderIncludes.val($("<div/>").html($dataTable.cell(selected_row_index, 3).data()).text());
            }
        }
        return true;
    }
});