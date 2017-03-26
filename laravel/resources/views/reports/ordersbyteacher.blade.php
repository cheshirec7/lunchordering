<style>
    .table {
        width: 550px;
        margin: 30px auto 20px;
    }

    td:first-child {
        width: 12px !important;
        padding: 5px !important;
    }

    .square {
        padding: 6px;
        border: 1px solid #999;
    }
</style>
<div class="text-center">
    <h2>{!! $title !!}</h2>
    <h4>{!! $thedate !!}</h4>
</div>
<?php $teacher = ''; ?>
<table class="table table-bordered table-striped table-condensed">
    @foreach($items as $item)
        @if ($teacher != $item->t_lname.$item->t_fname)
            @if ($item->t_lname == '(unassigned)')
                <tr>
                    <th colspan="3">No teacher assigned</th>
                </tr>
            @else
                <tr>
                    <th colspan="3">{!! $item->t_lname !!}, {!! $item->t_fname !!}</th>
                </tr>
            @endif
            <?php $teacher = $item->t_lname . $item->t_fname; ?>
        @endif
        <tr>
            <td>
                <div class="square"></div>
            </td>
            <td>{!! $item->o_fname !!} {!! $item->o_lname !!}</td>
            <td>{!! $item->short_desc !!}</td>
        </tr>
    @endforeach
</table>
<br/>
<p class="curdate">Generated {!! date('l, F jS, Y h:i:s A') !!}</p>