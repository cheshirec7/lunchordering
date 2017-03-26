$(function () {
    'use strict';

    var $grpAccounts = $("#grpAccounts").hide(),
        $grpDates = $("#grpDates").hide(),
        $selAccounts = $("#selAccounts").val(0).change(function (e) {
            handleButton($selAccounts.val() != 0);
        }),
        $selDates = $("#selDates").val(0).change(function (e) {
            handleButton($selDates.val() != 0);
        }),

        $selReports = $("#selReports").change(function (e) {
            $grpAccounts.hide();
            $grpDates.hide();
            $selDates.val(0);
            $selAccounts.val(0);
            handleButton(false);
            var rpt = +($selReports.val());
            switch (rpt) {
                case 1:
                    $grpDates.show();
                    break; //Lunch Orders By Provider
                // case 2: $grpDates.show(); break;//Lunch Orders By Teacher
                case 2:
                    $grpDates.show();
                    break; //Lunch Orders By Grade
                case 3:
                    handleButton(true);
                    break; //Account Balances
                case 4:
                    $grpAccounts.show();
                    break;//Account Details
                case 5:
                    $grpDates.show();
                    break; //Lunch Labels
            }
        }),
        $btnGo = $("#btnGo").click(function (e) {
            var rpt = +($selReports.val());
            switch (rpt) {
                case 1:
                    window.open('report?no=' + rpt + '&d=' + $selDates.val());
                    break;
                // case 2: window.open('report?no='+rpt+'&d='+$selDates.val()); break;
                case 2:
                    window.open('report?no=' + rpt + '&d=' + $selDates.val());
                    break;
                case 3:
                    window.open('report?no=' + rpt);
                    break;
                case 4:
                    window.open('report?no=' + rpt + '&a=' + $selAccounts.val());
                    break;
                case 5:
                    window.open('report?no=' + rpt + '&d=' + $selDates.val());
                    break;
            }
        });

    function handleButton(enable) {
        if (enable)
            $btnGo.prop('disabled', false).removeClass('btn-default').addClass('btn-primary');
        else
            $btnGo.prop('disabled', true).removeClass('btn-primary').addClass('btn-default');
    }
});