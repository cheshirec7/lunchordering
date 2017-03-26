$(function () {
    'use strict';

    var $msgAlert = $('.msgAlert'),
        $form = $('#formMenuItem'),
        $inpProviderID = $("input[name='provider_id']"),
        $inpProviderName = $("#provider_name"),
        $inpMenuItemID = $("input[name='menuitem_id']"),
        $inpMenuItemPrice = $("input[name='price']"),
        $selMenuItemActive = $("select[name='active']"),
        idToFind = 0,
        formInitialSerialized = '',
        needDataTableRefresh = false,

        $inpMenuItemName = $("textarea[name='item_name']").keypress(function (e) {
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

        $selProviders = $("select[name='selectprovider']").val(0).change(function (e) {
            var pid = $selProviders.val();
            $inpProviderID.val(pid);
            $inpProviderName.text($("option:selected", $selProviders).text());
            if (pid > 0)
                $dataTable.ajax.url('/admin/menuitems/' + pid).load();
            else
                $dataTable.clear().draw();
            $.fn.handleButton($btnNew, pid > 0, 'new');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
        }),

        $btnNew = $('#btnNew').click(function (e) {
            $dataTable.$('tr.selected').removeClass('selected');
            $.fn.handleButton($btnEdit, false);
            $.fn.handleButton($btnDel, false);
            $inpMenuItemID.val(0);
            //$inpProviderName.val($("option:selected", $selProviders).text());
            $inpMenuItemName.val('');
            $inpMenuItemPrice.val('5.00');
            //$selMenuItemActive.val($("select[name='active'] option:first").val());
            $selMenuItemActive.prop('selectedIndex', 0);
            formInitialSerialized = 'new';
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'New', 'Menu Item');
        }),

        $btnEdit = $('#btnEdit').click(function (e) {
            formInitialSerialized = $form.serialize();
            $.fn.showModalForm($modal, $btnSave, $msgAlert, 'Edit', 'Menu Item');
        }),

        $btnDel = $('#btnDel').click(function (e) {

            if ($dataTable.page.info().recordsTotal == 1) {
                BootstrapDialog.alert('<br />Use "Delete Provider"<br />to remove Providers<br />with only a single attached Menu Item.<br /><br />');
                return;
            }

            BootstrapDialog.mydelete(
                '<br />You have selected to delete menu item:<br /><br /><div class="textBlue">' + $inpMenuItemName.val() + '</div>' +
                //'<br />You will only be able to do this if there are no orders with this menu item.<br />'+
                '<br /><b>Are you sure?</b><br /><br />', function (result) {
                    if (result) {
                        $.ajax({
                            type: 'DELETE',
                            url: '/admin/menuitems/' + $inpMenuItemID.val()
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
            $inpMenuItemName.val($.trim($inpMenuItemName.val()));
            $inpMenuItemPrice.val($.trim($inpMenuItemPrice.val()));

            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/menuitems',
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

        $modal = $('#modalMenuItem').on('shown.bs.modal', function () {
            $inpMenuItemPrice.focus();
            $inpMenuItemName.focus();
        }).on('hide.bs.modal', function () {
            if (needDataTableRefresh) {
                needDataTableRefresh = false;

                $dataTable.ajax.reload(function (json) {
                    if (idToFind <= 0)
                        return;

                    var pos = $dataTable.order([0, 'asc']).row('#' + idToFind).index();
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

        $dataTable = $('#tableMenuItems').DataTable({
            dom: 'tip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                type: 'GET',
                data: {view: 1},
                url: '/admin/menuitems/0',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            order: [[0, 'asc']],
            language: {
                search: 'Search',
                emptyTable: '<i>Select a provider</i>'
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
        }).clear();

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
            $inpMenuItemID.val($dataTable.row($this).id());
            $inpMenuItemName.val($("<div/>").html($dataTable.cell(selected_row_index, 0).data()).text());
            $inpMenuItemPrice.val($dataTable.cell(selected_row_index, 1).data().slice(1));
            if ($.trim($dataTable.cell(selected_row_index, 2).data()) == 'No')
                $selMenuItemActive.val(0);
            else
                $selMenuItemActive.val(1);
        }
        return true;
    }
});