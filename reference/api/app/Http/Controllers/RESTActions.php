<?php namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


trait RESTActions {

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

    public function view ($id)
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

        $model->fill($request->input());
        if (in_array('user', $m::$RELATIONSHIPS['belongs_to'])) {
            $model->user()->associate(Auth::user());
        }
        $model->save();
        $model->makeHidden('user_id')->toArray();
        $value = [
            'data' => [
                'type' => $m::TYPE,
                'id' => $model->id,
                'attributes' => $model,
                'links' => [
                    'self' => $request->url() . '/' . $model->id
                ],
                'relationships' => [
                    'user' => [
                        'links' => [
                            'self' => '',
                            'related' => ''
                        ],
                        'data' => [
                            'id' => $model->user->id,
                            'type' => 'user'
                        ]
                    ],
                ]
            ],
            'included' => [
                [
                    'type' => 'user',
                    'id' => $model->user->id,
                    'attributes' => $model->user
                ]
            ]
        ];
        return $this->respond('created', $value);
    }

    public function update (Request $request, $id)
    {
        $m = self::MODEL;
        $this->validate($request, $m::$VALIDATION);
        $model = $m::find($id);
        if(is_null($model)){
            return $this->respond('not_found');
        }
        // if (Gate::allowes())
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