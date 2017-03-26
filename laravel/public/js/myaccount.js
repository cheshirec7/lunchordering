$(function () {
    'use strict';
    var $orderees = $('#orderees').change(function (e) {
            $ordersTable.ajax.url('/myaccount/orders/' + $orderees.val()).load(function (json) {
            })
        }),

        $ordersTable = $('#tblOrders').DataTable({
            dom: 'tip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                type: 'GET',
                url: '/myaccount/orders/' + $orderees.val(),
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            order: [[0, 'asc']],
            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_&nbsp; records per page',
                emptyTable: 'No orders found'
            },
            lengthMenu: [[10], ['10']],
            drawCallback: function () {
                $.fn.doPaginationAndLength(this);
            }
        }),

        $paymentsTable = $('#tblPayments').DataTable({
            dom: 'tip',
            processing: false,
            serverSide: false,
            autoWidth: false,
            ajax: {
                type: 'GET',
                url: '/myaccount/payments',
                error: function (xhr, err, thrown) {
                    if (err == 'parsererror')
                        location.reload();
                }
            },
            order: [[2, 'asc']],
            language: {
                search: 'Search',
                lengthMenu: 'Show &nbsp;_MENU_&nbsp; records per page',
                emptyTable: 'No payments found'
            },
            lengthMenu: [[10], ['10']],
            drawCallback: function () {
                $.fn.doPaginationAndLength(this);
            }
        });
});
