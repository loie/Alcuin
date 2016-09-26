<?php namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


trait RESTActions {

    private function getGateName (Request $request, $model) {
        $gateName = null;
        switch ($request->method()) {
            case 'GET':
                $gateName = 'read';
                break;
            case 'POST':
                $gateName = 'create';
                break;
            case 'PUT':
                $gateName = 'update';
                break;
            case 'DELETE':
                $gateName = 'delete';
                break;
            default:
                break;
        }
        $modelNames = explode('\\', $model);
        $modelName = strtolower($modelNames[count($modelNames) - 1]);
        $gateName .= '-' . $modelName;
        return $gateName;
    }

    protected $statusCodes = [
        'done' => 200,
        'created' => 201,
        'removed' => 204,
        'not_valid' => 400,
        'not_allowed' => 401,
        'not_found' => 404,
        'conflict' => 409,
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

        $model = new $m;

        $gateName = $this->getGateName($request, $m);
        if (Gate::allows($gateName, $this)) {
            echo 'allowed';
            $model->fill($request->input());
            if (in_array('user', $m::$RELATIONSHIPS['belongs_to'])) {
                $model->user()->associate(Auth::user());
            }

            $model->save();
            $value = [
                'data' => $model->makeHidden('user_id')->toArray()
            ];
            return $this->respond('created', $value);
        } else {
            $value = [
                'error' => 'Not allowed. This user has no permission to create this resource.'
            ];
            return $this->respond('not_allowed', $value);
        }

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