<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MenuItem;
use App\Repositories\MenuItemRepository;
use App\Repositories\ProviderRepository;
use Datatables;
use Illuminate\Http\Request;
use Validator;

class MenuItemController extends Controller
{
    protected $menuitems;
    protected $providers;

    /**
     * Create a new controller instance.
     *
     * @param  MenuItemRepository $menuitems
     * @param  ProviderRepository $providers
     */
    public function __construct(MenuItemRepository $menuitems, ProviderRepository $providers)
    {
        $this->menuitems = $menuitems;
        $this->providers = $providers;
    }

    /*****/
    public function index(Request $request)
    {
        $providers = $this->providers->datatableAllEditable()->get();
        return view('admin.menuitems.index', ['providers' => $providers]);
    }

    /*****/
    public function show($id, Request $request)
    {
        $provider_id = intval($id);

        if ($provider_id == 0) {
            $output = array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            );
            return response()->json($output);
        }

        switch (intval($request->view)) {
            case 1:
                $mymenuitems = $this->menuitems->datatableForProviderAll($provider_id);
                break;
            case 2:
                $mymenuitems = $this->menuitems->datatableForProviderActiveOnly($provider_id);
                break;
            case 3:
                $mymenuitems = $this->menuitems->datatableForProviderAsOfDate($provider_id, $request->date);
                break;
            default:
                $mymenuitems = $this->menuitems->datatableForProviderAll($provider_id);
                break;
        }

        return Datatables::of($mymenuitems)
            ->edit_column('price', '${{ number_format($price/100,2) }}')
            ->edit_column('active', '@if($active == 1) Yes @else No @endif')
            ->make();
    }

    /*****/
    public function store(Request $request)
    {
        $inputs = $request->only('menuitem_id', 'provider_id', 'item_name', 'price', 'active');
        $item_name = filter_var($inputs['item_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);

        $rules = array(
            'item_name' => array('required', 'min:2', 'max:100'),
            'price' => array('required', 'numeric', 'min:0', 'max:10')
        );

        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));
        }

        $menuitem_id = intval($inputs['menuitem_id']);
        $provider_id = intval($inputs['provider_id']);

        $mi_byname = MenuItem::where('item_name', $item_name)
            ->where('provider_id', $provider_id)
            ->where('id', '!=', $menuitem_id)
            ->first();

        if ($mi_byname)
            return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'That item already exists.'));

        if ($menuitem_id == 0) { //create new
            $mi = new MenuItem();
            $mi->provider_id = $provider_id;
        } else { //editing
            $mi = MenuItem::find($menuitem_id);
            if (!$mi)
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit. This menu item no longer exists.'));
        }

        $mi->item_name = $item_name;
        $mi->price = $inputs['price'] * 100;
        $mi->active = $inputs['active'];

        if ($mi->save()) {
            return response()->json(array('error' => false, 'idToFind' => $mi->id));
        } else
            return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to save menu item.'));
    }

    /*****/
    public function destroy($id, Request $request)
    {
        $menuitem_id = intval($id);
        //$provider_id = intval($request->account_id);

        //if ($menuitem_id > 0 && $provider_id > 0) {
        if ($menuitem_id > 0) {
            try {
                MenuItem::destroy($menuitem_id); //cascade deletes lunchdate_menuitems
                return response()->json(array('error' => false));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                if ($e->getCode() == 23000) {
                    return response()->json(array('error' => true, 'msg' => 'Unable to delete menu item: related orders must be removed first.'));
                } else {
                    return response()->json(array('error' => true, 'msg' => $e->getMessage()));
                }
            }
        }
        return response()->json(array('error' => true, 'msg' => 'Invalid provider or menu item id'));
    }
}
