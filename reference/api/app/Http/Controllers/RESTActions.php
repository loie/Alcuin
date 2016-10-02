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
        var_dump($id);

        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)){
            return $this->respond('not_found');
        }
        $relations = [];
        $includes = [];
        $all_relations = array_merge($m::$RELATIONSHIPS['belongs_to'], $m::$RELATIONSHIPS['has_many'], $m::$RELATIONSHIPS['belongs_to_and_has_many']);
        foreach ($all_relations as $name => $description) {
            $relations = $model->{$name}; // e.g. comments
            if ($relations !== null) {
                if (array_key_exists($name, $m::$RELATIONSHIPS['has_many']) || array_key_exists($name, $m::$RELATIONSHIPS['belongs_to_and_has_many'])) {
                    $relationship_array = [];
                    foreach($relations as $relation) {
            $item = get_relation_item_array($request, $description, $relation);
        }
        $value = [];
        $value['relationships'] => $item['relationship_item'];
        return $this->respond('done', $model);
    }

    private function get_relation_item_array($request, $description, $relation) {
        $relation_item = [];

        $link = $request->root() . config('names.path.' . $description['id']). '/' . $relation->id;
        $relation_item['links'] = [
            'self' => $link
        ];
        $relation_item['attributes'] = [
            'id' => $relation->id,
            'type' => $description['id'],
            'attributes' => $relation
        ];

        $inclusion_item = [];
        $inclusion_item['type'] = $description['id'];
        $inclusion_item['id'] = $relation->id;
        $inclusion_item['attributes'] = $relation;
        $inclusion_item['links'] = ['self' => $link];
        return [
            'relation_item' => $relation_item,
            'inclusion_item' => $inclusion_item
        ];
    }

    public function create (Request $request)
    {
        $m = self::MODEL;
        $this->validate($request, $m::$VALIDATION);

        $model = new $m;
        $model->fill($request->input());
        if (array_key_exists('user', $m::$RELATIONSHIPS['belongs_to'])) {
            $model->user()->associate($request->user());
        }
        $relations = array_keys($m::$RELATIONSHIPS['belongs_to']);
        $save_relations = function ($relation, $key) use ($model, $request) {
            if ($request->has($relation)) {
                $id = $request->input($relation);
                $className = 'App\\' . config('names.class.' . $relation);
                if (is_numeric($id)) { // belongs to relationship
                    // $assoc = $className::find($id);
                    $model->{$relation}()->associate($id);
                } else if (is_array($id)) {
                    $model->{$relation}()->attach($id);
                }
            }
        };
        array_walk($relations, $save_relations);
        try {
            $model->save();
        } catch (Exception $e) {
            $value = [
                'error' => 'Bad Request',
                'details' => 'Could not do this because the given model was invalid.'
            ];
            $this->respond('not_valid', $value);
        }

        // handle m:n relation
        $relations = array_keys($m::$RELATIONSHIPS['belongs_to_and_has_many']);
        array_walk($relations, $save_relations);
        $model->save();

        // handle output
        $model->makeHidden('user_id')->toArray();
        $relationships = [];
        $included = [];
        // $handle_relationship = function ($description, $name) use ($relationships, $included, $model, $request) {
        // };

        // array_walk($m::$RELATIONSHIPS['belongs_to'], $handle_relationship);
        // var_dump($relationships);

        // $walk_relations = function ($value, $key)
        $all_relations = array_merge($m::$RELATIONSHIPS['belongs_to'], $m::$RELATIONSHIPS['has_many'], $m::$RELATIONSHIPS['belongs_to_and_has_many']);
        foreach ($all_relations as $name => $description) {
            $relations = $model->{$name}; // e.g. comments
            if ($relations !== null) {
                if (array_key_exists($name, $m::$RELATIONSHIPS['has_many']) || array_key_exists($name, $m::$RELATIONSHIPS['belongs_to_and_has_many'])) {
                    $relationship_array = [];
                    foreach($relations as $relation) {
                        $items = $this->get_relation_item_array($request, $description, $relation);
                        array_push($relationship_array, $items['relation_item']);
                        array_push($included, $items['inclusion_item']);
                    }
                    if (count($relationship_array) > 0) {
                        $relationships[$name] = $relationship_array;
                    }
                } else if (array_key_exists($name, $m::$RELATIONSHIPS['belongs_to'])) {
                    $relation = $relations;
                    $item = $this->get_relation_item_array($request, $description, $relation);
                    $relationships[$name] = $item['relation_item'];
                    array_push($included, $item['inclusion_item']);
                }
            }
        }
        $value = [
            'data' => [
                'type' => $m::TYPE,
                'id' => $model->id,
                'attributes' => $model,
                'links' => [
                    'self' => $request->url() . '/' . $model->id
                ],
                'relationships' => $relationships
            ],
            'included' => $included
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