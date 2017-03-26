<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
//use App\Http\Requests;
use App\MenuItem;
use App\Provider;
use App\Repositories\ProviderRepository;
use Datatables;
use Illuminate\Http\Request;
use Validator;

class ProviderController extends Controller
{
    protected $providers;

    /**
     * Create a new controller instance.
     *
     * @param  ProviderRepository $providers
     */
    public function __construct(ProviderRepository $providers)
    {
        $this->providers = $providers;
    }

    /*****/
    public function index(Request $request)
    {
        $valid_extensions = ['png', 'jpg'];
        $files = \File::files(config('app.provider_image_directory'));
        $validfiles = array();
        foreach ($files as $file) {
            $extension = \File::extension($file);
            if (in_array($extension, $valid_extensions))
                $validfiles[] = basename($file);
        }
        return view('admin.providers.index', ['files' => $validfiles]);
    }

    /*****/
    public function show($id)
    {
        $providers = $this->providers->datatableAllEditable();
        return Datatables::of($providers)->make();
    }

    /*****/
    public function store(Request $request)
    {
        $inputs = $request->only('provider_id', 'provider_name', 'provider_image', 'provider_url', 'provider_includes');
        $inputs['provider_name'] = filter_var($inputs['provider_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
        $inputs['provider_url'] = filter_var($inputs['provider_url'], FILTER_SANITIZE_URL);
        $inputs['provider_image'] = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $inputs['provider_image']);
        $inputs['provider_image'] = preg_replace("([\.]{2,})", '', $inputs['provider_image']);
        //$inputs['provider_image'] = filter_var($inputs['provider_image'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);

        if (preg_match("#https?://#", $inputs['provider_url']) === 0)
            $inputs['provider_url'] = 'http://' . $inputs['provider_url'];

        $rules = array(
            'provider_name' => array('required', 'min:2', 'max:50', 'unique' => 'unique:los_providers,provider_name'),
            'provider_image' => array('required', 'min:2', 'max:50'),
            'provider_url' => array('required', 'min:2', 'max:255', 'url'),
            'provider_includes' => array('max:255')
        );

        $editing = false;
        $provider_id = intval($inputs['provider_id']);
        if ($provider_id > 0) {
            $provider = Provider::find($provider_id);

            if (!$provider)
                return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to edit. This provider no longer exists.'));

            $rules['provider_name']['unique'] .= ',' . $provider_id;
            $editing = true;
        } else
            $provider = new Provider();

        $validator = Validator::make($inputs, $rules);
        if ($validator->fails())
            return response()->json(array('error' => true, 'needrefresh' => false, 'msg' => $validator->messages()->first()));

        $provider->provider_name = $inputs['provider_name'];
        $provider->provider_image = $inputs['provider_image'];
        $provider->provider_url = $inputs['provider_url'];
        $provider->provider_includes = $inputs['provider_includes'];

        if (!$provider->save())
            return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to save provider.'));

        if ($editing)
            return response()->json(array('error' => false, 'idToFind' => $provider->id));

        $mi = new MenuItem();
        $mi->provider_id = $provider->id;
        $mi->item_name = '[temp item]';
        $mi->price = config('app.menuitem_default_price');
        $mi->active = 1;

        if ($mi->save())
            return response()->json(array('error' => false, 'idToFind' => $provider->id));

        return response()->json(array('error' => true, 'needrefresh' => true, 'msg' => 'Unable to save menu item.'));
    }

    /*****/
    public function destroy($id)
    {
        $provider_id = intval($id);
        //cascade delete on menuitems, restrict delete on orderdetails
        try {
            Provider::destroy($provider_id);
            return response()->json(array('error' => false));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            if ($e->getCode() == 23000) {
                return response()->json(array('error' => true, 'msg' => 'Unable to delete provider: related orders must be removed first.'));
            } else {
                return response()->json(array('error' => true, 'msg' => $e->getMessage()));
            }
        }
    }
}
