<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\AccountRepository;
use App\Repositories\LunchDateRepository;
use App\Repositories\OrderDetailRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProviderRepository;
use Codedge\Fpdf\Fpdf\FPDF;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * @var AccountRepository $accounts
     * @var LunchDateRepository $lunchdates
     * @var OrderRepository $orders
     * @var OrderDetailRepository $orderdetails
     * @var ProviderRepository $providers
     * @var PaymentRepository $payments
     * @var FPDF $fpdf
     */

    protected $accounts;
    protected $lunchdates;
    protected $orders;
    protected $orderdetails;
    protected $providers;
    protected $payments;
    protected $fpdf;

    protected $_Margin_Left;    // Left margin of labels
    protected $_Margin_Top;     // Top margin of labels
    protected $_X_Space;        // Horizontal space between 2 labels
    protected $_Y_Space;        // Vertical space between 2 labels
    protected $_X_Number;       // Number of labels horizontally
    protected $_Y_Number;       // Number of labels vertically
    protected $_Width;          // Width of label
    protected $_Height;         // Height of label
    protected $_Line_Height;    // Line height
    protected $_Padding;        // Padding
    protected $_Metric_Doc;     // Type of metric for the document
    protected $_COUNTX;         // Current x position
    protected $_COUNTY;         // Current y position

    // List of label formats
    protected $_Avery_Labels = array(
        '5160' => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 1.762, 'marginTop' => 10.7, 'NX' => 3, 'NY' => 10, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 66.675, 'height' => 25.4, 'font-size' => 8),

        '5160-adj' => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 1.762, 'marginTop' => 13.7, 'NX' => 3, 'NY' => 10, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 66.675, 'height' => 27.5, 'font-size' => 8),

        '5161' => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 0.967, 'marginTop' => 10.7, 'NX' => 2, 'NY' => 10, 'SpaceX' => 3.967, 'SpaceY' => 0, 'width' => 101.6, 'height' => 25.4, 'font-size' => 8),
        '5162' => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 0.97, 'marginTop' => 20.224, 'NX' => 2, 'NY' => 7, 'SpaceX' => 4.762, 'SpaceY' => 0, 'width' => 100.807, 'height' => 35.72, 'font-size' => 8),
        '5163' => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 1.762, 'marginTop' => 10.7, 'NX' => 2, 'NY' => 5, 'SpaceX' => 3.175, 'SpaceY' => 0, 'width' => 101.6, 'height' => 50.8, 'font-size' => 8),
        '5164' => array('paper-size' => 'letter', 'metric' => 'in', 'marginLeft' => 0.148, 'marginTop' => 0.5, 'NX' => 2, 'NY' => 3, 'SpaceX' => 0.2031, 'SpaceY' => 0, 'width' => 4.0, 'height' => 3.33, 'font-size' => 12),
        '8600' => array('paper-size' => 'letter', 'metric' => 'mm', 'marginLeft' => 7.1, 'marginTop' => 19, 'NX' => 3, 'NY' => 10, 'SpaceX' => 9.5, 'SpaceY' => 3.1, 'width' => 66.6, 'height' => 25.4, 'font-size' => 8),
        'L7163' => array('paper-size' => 'A4', 'metric' => 'mm', 'marginLeft' => 5, 'marginTop' => 15, 'NX' => 2, 'NY' => 7, 'SpaceX' => 25, 'SpaceY' => 0, 'width' => 99.1, 'height' => 38.1, 'font-size' => 9),
        '3422' => array('paper-size' => 'A4', 'metric' => 'mm', 'marginLeft' => 0, 'marginTop' => 8.5, 'NX' => 3, 'NY' => 8, 'SpaceX' => 0, 'SpaceY' => 0, 'width' => 70, 'height' => 35, 'font-size' => 9)
    );

    /**
     * Create a new controller instance.
     *
     * @param  AccountRepository $accounts
     * @param  LunchDateRepository $lunchdates
     * @param  OrderRepository $orders
     * @param  OrderDetailRepository $orderdetails
     * @param  ProviderRepository $providers
     * @param  PaymentRepository $payments
     */
    public function __construct(AccountRepository $accounts,
                                LunchDateRepository $lunchdates,
                                OrderRepository $orders,
                                OrderDetailRepository $orderdetails,
                                ProviderRepository $providers,
    PaymentRepository $payments,
                                FPDF $fpdf)
    {
        $this->accounts = $accounts;
        $this->lunchdates = $lunchdates;
        $this->orders = $orders;
        $this->orderdetails = $orderdetails;
        $this->providers = $providers;
        $this->payments = $payments;
        $this->fpdf = $fpdf;
    }

    /**
     * Display report selection.
     * @param  Request $request
     * @return view
     */
    public function index(Request $request)
    {
        $accs = $this->accounts->activeAccounts();
        $dates = $this->lunchdates->datesWithOrders();
        return view('reports.adminindex', ['accounts' => $accs, 'dates' => $dates]);
    }

    public function doReport(Request $request)
    {
        switch ($request->no) {

            case 1:
                $title = 'Lunch Orders By Provider';
                $date = date_create($request->d)->format('l, F jS, Y');
                $provider = $this->providers->providerForDate($request->d);
                $items = $this->orders->adminProviderReport($request->d);
                return view('reports.providerorders', ['title' => $title, 'thedate' => $date, 'provider' => $provider, 'items' => $items])->render();
                break;

//            case 2: $title = 'Lunch Orders By Teacher';
//                $date = date_create($request->d)->format('l, F jS, Y');
//                $items = $this->orders->adminOrdersByTeacherReport($request->d);
//                return view('admin.reports.ordersbyteacher', ['title'=> $title, 'thedate'=>$date, 'items'=>$items])->render();
//                break;

            case 2:
                $title = 'Lunch Orders By Grade';
                $date = date_create($request->d)->format('l, F jS, Y');
                $provider = $this->providers->providerForDate($request->d);
                $items = $this->orders->adminOrdersByGradeReport($request->d);
                return view('reports.ordersbygrade', ['title' => $title, 'thedate' => $date, 'provider' => $provider, 'items' => $items])->render();
                break;

            case 3:
                $title = 'Account Balances';
                $items = $this->accounts->adminAccountBalancesReport();
                return view('reports.accountbalances', ['title' => $title, 'items' => $items])->render();
                break;

            case 4:
                $title = 'Account Details';
                $account = $this->accounts->adminAccountDetailReport($request->a);
                $payments = $this->payments->adminAccountDetailReport($request->a);
                $orders = $this->orderdetails->adminAccountDetailReport($request->a);
                return view('reports.accountdetails', ['title' => $title, 'account' => $account, 'payments' => $payments, 'orders' => $orders])->render();
                break;

            case 5: //Avery 5160 labels

                $items = $this->orders->adminLunchLabels($request->d);
                $Tformat = $this->_Avery_Labels['5160-adj'];
                $this->_Metric_Doc = "mm";
                $this->_COUNTX = -1;
                $this->_COUNTY = 0;
                $this->_Set_Format($Tformat);
                $this->fpdf->SetFont('Arial');
                $this->Set_Font_Size(8);
                $this->fpdf->SetMargins(0, 0);
                $this->fpdf->SetAutoPageBreak(false);
                $this->fpdf->AddPage();
                foreach ($items as $item) {
                    if ($item->grade_desc == '(unassigned)')
                        $text = sprintf("%s %s\n%s", $item->first_name, $item->last_name, $item->short_desc);
                    else
                        $text = sprintf("%s\n%s %s\n%s", $item->grade_desc, $item->first_name, $item->last_name, $item->short_desc);
                    $this->Add_Label($text);
                }
                $this->fpdf->Output('D', 'lunchlabels' . $request->d . '.pdf');

                //return view('reports.lunchlabels', ['items'=>$items])->render();
                break;
        }

        return 'Invalid Report';
    }

    private function _Set_Format($format)
    {
        $this->_Margin_Left = $this->_Convert_Metric($format['marginLeft'], $format['metric']);
        $this->_Margin_Top = $this->_Convert_Metric($format['marginTop'], $format['metric']);
        $this->_X_Space = $this->_Convert_Metric($format['SpaceX'], $format['metric']);
        $this->_Y_Space = $this->_Convert_Metric($format['SpaceY'], $format['metric']);
        $this->_X_Number = $format['NX'];
        $this->_Y_Number = $format['NY'];
        $this->_Width = $this->_Convert_Metric($format['width'], $format['metric']);
        $this->_Height = $this->_Convert_Metric($format['height'], $format['metric']);
        $this->_Padding = $this->_Convert_Metric(3, 'mm');
    }

    private function _Convert_Metric($value, $src)
    {
        $dest = $this->_Metric_Doc;
        if ($src != $dest) {
            $a['in'] = 39.37008;
            $a['mm'] = 1000;
            return $value * $a[$dest] / $a[$src];
        } else {
            return $value;
        }
    }

    function Set_Font_Size($pt)
    {
        $this->_Line_Height = $this->_Get_Height_Chars($pt);
        $this->fpdf->SetFontSize($pt);
    }

    function _Get_Height_Chars($pt)
    {
        $a = array(6 => 2, 7 => 2.5, 8 => 3, 9 => 4, 10 => 5, 11 => 6, 12 => 7, 13 => 8, 14 => 9, 15 => 10);
        if (!isset($a[$pt]))
            $this->Error('Invalid font size: ' . $pt);
        return $this->_Convert_Metric($a[$pt], 'mm');
    }

    private function Add_Label($text)
    {
        $this->_COUNTX++;
        if ($this->_COUNTX == $this->_X_Number) {
            // Row full, we start a new one
            $this->_COUNTX = 0;
            $this->_COUNTY++;
            if ($this->_COUNTY == $this->_Y_Number) {
                // End of page reached, we start a new one
                $this->_COUNTY = 0;
                $this->fpdf->AddPage();
            }
        }

        $_PosX = $this->_Margin_Left + $this->_COUNTX * ($this->_Width + $this->_X_Space) + $this->_Padding;
        $_PosY = $this->_Margin_Top + $this->_COUNTY * ($this->_Height + $this->_Y_Space) + $this->_Padding;
        $this->fpdf->SetXY($_PosX, $_PosY);
        $this->fpdf->MultiCell($this->_Width - $this->_Padding, $this->_Line_Height, $text, 0, 'L');
    }
}
