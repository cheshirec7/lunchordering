$(function () {
    'use strict';
    var formInitialSerialized = '',
        curPeriodNo = 0,
        displayPeriod = 'week',
        $periodDesc = $("#perioddesc"),
        $modalheaderbody = $("#modal-header-body"),
        $amountdue = $('#amountdue'),
        globalObj = {},

        $cboxes = $(document).on('click', 'input[type="checkbox"]', function (e) {
            recalcTotal($(this).data('id'));
        }),

        $qtys = $(document).on('change', 'input[type="number"]', function (e) {
            var $this = $(this);
            if ($this.val() < 1 || $this.val() > 2) {
                $this.val(1);
            }
            recalcTotal(0);
        }),

        $form = $('#orderform').submit(function (e) {
            e.preventDefault();

            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/orders',
                    data: formCurSerialized + '&acctid=' + $selAccountNames.val() +
                    '&uid=' + globalObj.cur_userid + '&ts=' + globalObj.cur_ts + '&lunchdateid=' + globalObj.cur_lunchdateid,
                    dataType: 'json'
                }).done(function (data, textStatus, jqXHR) {
                    if (data.error) {
                        location.reload();
                    } else {
                        globalObj.$cell_being_edited.replaceWith(data.html);
                        $amountdue.text(data.amtdue);
                        $modal.modal('hide');
                    }
                });
            }
        }),

        $todayis = $('.returntotoday').click(function (e) {
            if (curPeriodNo != 0)
                getLunchCalendar(-curPeriodNo);
        }),

        $modal = $('#modalOrderLunch').on("keypress", function (e) {
            if (e.which == 13)
                $form.submit();
        }),

        $caroLunches = $("#caroLunches").carousel({interval: 0}).on('slid.bs.carousel', function () {
            $('.carousel-item', $caroLunches).remove();
            $('.carousel-inner', $caroLunches).append(globalObj.newitem);
            $('.carousel-item', $caroLunches).addClass('active');
        }).on('click', 'button.clickablearea', function () {
            var $this = $(this);

            globalObj.cur_ts = $this.data('ts');
            globalObj.cur_userid = $this.data('userid');
            globalObj.cur_lunchdateid = $this.data('lunchdateid');
            globalObj.$cell_being_edited = $this.parent();

            $.ajax({
                type: 'GET',
                url: '/orders/menu',
                data: {'uid': globalObj.cur_userid, 'ts': globalObj.cur_ts},
                dataType: 'json'
            }).done(function (data) {
                $modalheaderbody.html(data.html);
                formInitialSerialized = $form.serialize();
                $modal.modal('show');
            });
        }),

        $selAccountNames = $("select[name='accountnames']").change(function () {
            getLunchCalendar(-curPeriodNo);
        }),

        $btnPrev = $(".navbtn.prev").click(function (e) {
            getLunchCalendar(-1);
        }),
        $btnNext = $(".navbtn.next").click(function (e) {
            getLunchCalendar(1);
        });

    function recalcTotal(id) {
        var total = 0;
        $("input[type='number']").each(function (index) {
            var $this = $(this);
            if (this.name == id) {
                if (this.disabled)
                    $this.val(1);
                else
                    $this.val('');
                this.disabled = !this.disabled;
            }
            total += $this.val() * $this.data('price');
        }).promise().done(function () {
            total /= 100;
            $('#ordertotal').text('$' + total.toFixed(2));
        });
    }

    /////
    function getLunchCalendar(numPeriodsToScroll) {

        var itemstart = '<div class="carousel-item">',
            itemend = '</div>',
            periodDesc = '';

        $.ajax({
            type: 'GET',
            url: '/orders/' + $selAccountNames.val(),
            data: {period: curPeriodNo + numPeriodsToScroll, displayPeriod: displayPeriod}
        }).done(function (d) {
            globalObj.newitem = itemstart + d + itemend;
            if (numPeriodsToScroll < 0) {
                $('.active', $caroLunches).parent().prepend(globalObj.newitem);
                $caroLunches.carousel('prev');
            } else {
                $('.active', $caroLunches).parent().append(globalObj.newitem);
                $caroLunches.carousel('next');
            }

            curPeriodNo += numPeriodsToScroll;

            switch (curPeriodNo) {
                case 0:
                    periodDesc = 'This ' + displayPeriod;
                    break;
                case 1:
                    periodDesc = 'Next ' + displayPeriod;
                    break;
                case -1:
                    periodDesc = 'Last ' + displayPeriod;
                    break;
                default:
                    if (curPeriodNo > 1)
                        periodDesc = curPeriodNo + ' ' + displayPeriod + 's from now';
                    else
                        periodDesc = Math.abs(curPeriodNo) + ' ' + displayPeriod + 's ago';
                    break;
            }

            $periodDesc.fadeOut(function () {
                $periodDesc.html(periodDesc).fadeIn();
            });
        })
    }
})