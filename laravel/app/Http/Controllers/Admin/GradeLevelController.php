<?php

namespace App\Http\Controllers\Admin;

use App\GradeLevel;
use App\Http\Controllers\Controller;
use App\Repositories\GradeLevelRepository;
use App\User;
use Datatables;
use Illuminate\Http\Request;
use Validator;

class GradeLevelController extends Controller
{
    protected $gradelevels;

    /**
     * Create a new controller instance.
     *
     * @param  GradeLevelRepository $gradelevels
     */
    public function __construct(GradeLevelRepository $gradelevels)
    {
        $this->gradelevels = $gradelevels;
    }

    /*****/
    public function index(Request $request)
    {
        return view('admin.gradelevels.index');
    }

    /*****/
    public function show($id)
    {
        $gradelevels = $this->gradelevels->datatableAllEditable();
        return Datatables::of($gradelevels)
//            ->add_column('actions', '<button class="btn btn-primary btn-xs" ><span class="glyphicon glyphicon-edit"></span></button>
//                    <button class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span></button>')
            ->make();
    }

    /*****/
    public function store(Request $request)
    {
        $inputs = $request->only('grade_id', 'grade', 'grade_desc', 'report_order');
        $inputs['grade'] = filter_var($inputs['grade'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $inputs['grade_desc'] = filter_var($inputs['grade_desc'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);

        $rules = array(
            'grade' => array('required', 'max:10', 'unique' => 'unique:los_gradelevels,grade'),
            'grade_desc' => array('required', 'min:2', 'max:50', 'unique' => 'unique:los_gradelevels,grade_desc'),
            'report_order' => array('required', 'integer', 'min:1', 'max:999')
        );

        $grade_id = intval($inputs['grade_id']);
        if ($grade_id > 0) {

            $gl = GradeLevel::find($grade_id);
            if (!$gl) {
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit. The grade no longer exists.'));
            }

            $rules['grade']['unique'] .= ',' . $grade_id;
            $rules['grade_desc']['unique'] .= ',' . $grade_id;
            //$rules['report_order']['unique'] .= ',' . $grade_id;
        } else
            $gl = new GradeLevel();

        $validator = Validator::make($inputs, $rules);
        if ($validator->passes()) {
            $gl->grade = $inputs['grade'];
            $gl->grade_desc = $inputs['grade_desc'];
            $gl->report_order = intval($inputs['report_order']);

            if ($gl->save()) {
                return response()->json(array('error' => false, 'idToFind' => $gl->id));
            } else
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to save grade level.'));
        } else
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));
    }

    /*****/
    public function destroy($id)
    {
        $grade_id = intval($id);

        if ($grade_id > 0) {
            $users_assigned = User::where('grade_id', $grade_id)->count();
            if ($users_assigned > 0) {
                $msg = 'Unable to delete: grade level is assigned to ' . $users_assigned . ' users.';
                return response()->json(array('error' => true, 'msg' => $msg));
            }

            try {
                GradeLevel::destroy($grade_id);
                return response()->json(array('error' => false));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                if ($e->getCode() == 23000) {
                    return response()->json(array('error' => true, 'msg' => 'Unable to delete grade level: users must be reassigned to another grade first.'));
                } else {
                    return response()->json(array('error' => true, 'msg' => $e->getMessage()));
                }
            }
        }
        return response()->json(array('error' => true, 'msg' => 'Invalid grade id'));
    }
}
