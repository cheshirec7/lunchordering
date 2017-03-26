$(function () {
    'use strict';

    var $monthYear = $("#schedmonthyear"),
        formInitialSerialized = '',
        cur_period_no = 0,
        new_carousel_item = '',
        $cell_being_edited = null,

        $modal = $('#modalScheduleDay').on('shown.bs.modal', function () {
            $taECMsg.focus();
            $taAddMsg.focus();
        }),

        $scheduledate = $('#scheduledate'),
        $ts = $('#ts', $modal),
        $taECMsg = $('#ecmsg', $modal),
        $taAddMsg = $('#addmsg', $modal),
        $menuitemscontainer = $('#menuitemscontainer', $modal),
        $scrollbox = $('#scrollbox', $modal),

        $tas = $("textarea").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $form.submit();
            }
        }),
        $inputs = $("input").keypress(function (e) {
            if (e.which == 13) {
                e.preventDefault();
                $form.submit();
            }
        }),

        $selProvider = $("#select_provider").on('change', function () {
            if ($selProvider.val() < 4) {
                $scrollbox.empty();
                $menuitemscontainer.hide();
            } else
                loadModal($selProvider.val(), 0);
        }),

        $form = $('#scheduledayform').submit(function (e) {
            e.preventDefault();

            var formCurSerialized = $form.serialize();
            if (formInitialSerialized == formCurSerialized) {
                $modal.modal('hide');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/admin/schedule',
                    data: formCurSerialized,
                    dataType: 'json'
                }).done(function (data) {
                    if (data.error) {
                        location.reload();
                    } else {
                        $cell_being_edited.empty().append(data.html);
                        $modal.modal('hide');
                    }
                });
            }
        }),

        $caroSchedule = $("#caroSchedule").carousel({interval: 0}).on('slid.bs.carousel', function () {
            $('.carousel-item', $caroSchedule).remove();
            $('.carousel-inner', $caroSchedule).append(new_carousel_item);
            $('.carousel-item', $caroSchedule).addClass('active');
        }).on('click', 'td.enabled', function () {
            $cell_being_edited = $(this);
            var timestamp = $cell_being_edited.data('ts');
            $ts.val(timestamp);
            $scheduledate.text(moment.unix(timestamp).format('dddd, MMMM D, YYYY'));
            loadModal(0, 1);
        }),

        $btnPrev = $(".navbtn.prev").click(function (e) {
            getSchedule(-1);
        }),
        $btnNext = $(".navbtn.next").click(function (e) {
            getSchedule(1);
        });

    /////
    function loadModal(selected_providerid, use_saved) {
        $.ajax({
            type: 'GET',
            url: '/admin/schedule/getProviderMenuItems',
            data: {
                ts: $cell_being_edited.data('ts'),
                pid: selected_providerid,
                use_saved: use_saved
            },
            dataType: 'json'
        }).done(function (data) {
            if (data.error) {
                location.reload();
            } else {
                $selProvider.val(data.pid);
                $scrollbox.html(data.html);

                if (data.pid < 4) {
                    $menuitemscontainer.hide();
                } else {
                    $menuitemscontainer.show();
                }

                if (data.orders_placed || data.has_orders)
                    $selProvider.attr('disabled', 'disabled')
                else
                    $selProvider.removeAttr('disabled');

                if (use_saved) {
                    $taAddMsg.val(data.add_text);
                    $taECMsg.val(data.ec_text);
                    formInitialSerialized = $form.serialize();
                    $modal.modal('show');
                }
            }
        });
    }

    /////
    function getSchedule(numPeriodsToScroll) {
        cur_period_no += numPeriodsToScroll;
        $.ajax({
            type: 'GET',
            url: '/admin/schedule/' + cur_period_no
        }).done(function (data) {
            if (data.error) {
                location.reload();
            } else {
                new_carousel_item = '<div class="carousel-item">' + data.html + '</div>';
                if (numPeriodsToScroll < 0) {
                    $('.active', $caroSchedule).parent().prepend(new_carousel_item);
                    $caroSchedule.carousel('prev');
                } else {
                    $('.active', $caroSchedule).parent().append(new_carousel_item);
                    $caroSchedule.carousel('next');
                }

                var monthYear = moment().startOf('month').add(cur_period_no, 'months').format('MMMM YYYY');

                $monthYear.fadeOut(function () {
                    $monthYear.text(monthYear).fadeIn();
                });
            }
        })
    }
});