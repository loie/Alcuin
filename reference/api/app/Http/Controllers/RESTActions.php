<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


trait RESTActions {

    protected $statusCodes = [
        'done' => 200,
        'created' => 201,
        'removed' => 204,
        'not_valid' => 400,
        'not_found' => 404,
        'conflict' => 409,
        'permissions' => 401
    ];

    public function all()
    {
        $m = self::MODEL;
        return $this->respond('done', $m::all());
    }

    public function read ($id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)){
            return $this->respond('not_found');
        }
        return $this->respond('done', $model);
    }

    public function create (Request $request)
    {
        $m = self::MODEL;
        $this->validate($request, $m::$VALIDATION);
        $properties_prepared = $request->all();
        $properties = [];

        // mockup: use real value
        foreach ($properties_prepared as $key => $value) {
            $properties[$key] = $value;
        }
        $properties['user_id'] = 2;
        return $this->respond('created', $m::create($properties));
    }

    public function update (Request $request, $id)
    {
        $m = self::MODEL;
        $this->validate($request, $m::$VALIDATION);
        $model = $m::find($id);
        if(is_null($model)){
            return $this->respond('not_found');
        }
        if (Gate::allowes())
        $model->update($request->all());
        return $this->respond('done', $model);
    }

    public function delete ($id)
    {
        $m = self::MODEL;
        if(is_null($m::find($id))){
            return $this->respond('not_found');
        }
        $m::destroy($id);
        return $this->respond('removed');
    }

    protected function respond ($status, $data = [])
    {
        return response()->json($data, $this->statusCodes[$status]);
    }

}