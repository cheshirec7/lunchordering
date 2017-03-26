<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Lunch Labels</title>
    <style>
        body {
            width: 8.5in;
            margin: 0 .1875in;
        }

        .label {
            width: 2.025in; /* plus .6 inches from padding */
            height: .875in; /* plus .125 inches from padding */
            padding: .125in .3in 0;
            margin-right: .125in; /* the gutter */
            float: left;
            text-align: center;
            overflow: hidden;
            outline: 1px dotted; /* outline doesn't occupy space like border does */
        }

        .page-break {
            clear: left;
            display: block;
            page-break-after: always;
        }

        input {
            display: block;
            padding: 10px;
            margin: 10px auto;
        }

        @media print {
            input {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<input type="button" onClick="window.print()" value="Print"/>

<?php $count = 0; ?>
@foreach($items as $item)
    <div class="label">
        {!! $item->first_name !!} {!! $item->last_name !!}<br/>
        {!! $item->grade_desc !!}<br/>
        {!! $item->short_desc !!}
    </div>
    <?php
    $count++;
    if ($count == 30) {
        echo '<div class="page-break"></div>';
        $count = 0;
    }
    ?>
@endforeach

</body>
</html>