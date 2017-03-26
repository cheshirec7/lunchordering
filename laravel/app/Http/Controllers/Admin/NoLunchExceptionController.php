<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\NoLunchException;
use App\Repositories\GradeLevelRepository;
use App\Repositories\NoLunchExceptionRepository;
use App\Repositories\UserRepository;
use Datatables;
use Illuminate\Http\Request;
use Validator;

class NoLunchExceptionController extends Controller
{
    protected $nles;
    protected $gradelevels;
    protected $teachers;

    /**
     * Create a new controller instance.
     *
     * @param  NoLunchExceptionRepository $nles
     * @param GradeLevelRepository $gradelevels
     * @param UserRepository $teachers
     */
    public function __construct(NoLunchExceptionRepository $nles, GradeLevelRepository $gradelevels, UserRepository $teachers)
    {
        $this->nles = $nles;
        $this->gradelevels = $gradelevels;
        $this->teachers = $teachers;
    }

    /*****/
    public function index(Request $request)
    {
        return view('admin.nolunchexceptions.index',
            ['gradelevels' => $this->gradelevels->assignedGradeLevels(),
                'teachers' => $this->teachers->assignedTeachers()]);
    }

    /*****/
    public function show($id)
    {
        $nles = $this->nles->datatableTodayAndLater();
        return Datatables::of($nles)
            ->remove_column('teacher_name')
            ->edit_column('teacher_grade', '@if($teacher_id!=1) {{ $teacher_name }} @else {{ $teacher_grade }} @endif')
            ->make();
    }

    /*****/
    public function store(Request $request)
    {
        $inputs = $request->only('nle_id', 'exception_date', 'exception_type', 'reason', 'description', 'teacher_id', 'grade_id');
        foreach ($inputs as &$value) {
            $value = trim($value);
        }

//      throws 500 error
//        try {
//            $exception_date = new \DateTime($inputs['exception_date']);
//        } catch (Exception $e) {
//            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'Date error: '.$e->getMessage()));
//        }

        $exception_date = date_create($inputs['exception_date']);
        $msg = '';
        if (!$exception_date) {
            $e = date_get_last_errors();
            foreach ($e['errors'] as $error) {
                $msg .= $error . ' ';
            }
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => 'Date error: ' . $msg));
        }

        $inputs['reason'] = filter_var($inputs['reason'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $inputs['description'] = filter_var($inputs['description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $teacher_id = intval($inputs['teacher_id']);
        $grade_id = intval($inputs['grade_id']);

        $rules = array(
            'exception_type' => array('required', 'integer', 'min:1', 'max:2'),
            'teacher_id' => array('required', 'integer', 'min:1'),
            'grade_id' => array('required', 'integer', 'min:1'),
            'reason' => array('required', 'min:2', 'max:30'),
            'description' => array('required', 'min:2', 'max:50')
        );

        $validator = Validator::make($inputs, $rules);
        if ($validator->passes()) {
            $ordercount1 = 0;//$this->nles->numOrdersDateTeacher($exception_date,$teacher_id);
            $ordercount2 = $this->nles->numOrdersDateGrade($exception_date, $grade_id);

//            \Log::debug($ordercount1);
//            \Log::debug($ordercount2);

            if ($ordercount1 > 0 || $ordercount2 > 0)
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Related orders already exist for this date/teacher/grade, which must be removed before this exception can be saved.'));

            $nle_id = intval($inputs['nle_id']);
            if ($nle_id > 0)
                NoLunchException::destroy($nle_id);
            $nle = new NoLunchException();
            $nle->exception_date = $exception_date;
            $nle->reason = $inputs['reason'];
            $nle->description = $inputs['description'];
            $nle->teacher_id = $teacher_id;
            $nle->grade_id = $grade_id;

            try {
                if ($nle->save()) {
                    return response()->json(array('error' => false, 'idToFind' => $nle->id));
                } else
                    return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to save exception.'));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                if ($e->getCode() == 23000)
                    return response()->json(array('error' => true, 'msg' => 'Unable to save. Exception date/teacher/grade already exists.'));
                else
                    return response()->json(array('error' => true, 'msg' => $e->getMessage()));
            }
        } else
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));
    }

    /*****/
    public function destroy($id)
    {
        $nle_id = intval($id);
        if ($nle_id > 0) {
            NoLunchException::destroy($nle_id);
            return response()->json(array('error' => false));
        }
        return response()->json(array('error' => true, 'msg' => 'Invalid exception id'));
    }
}
