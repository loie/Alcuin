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
        'unprocessable' => 422,
    ];

    private function get_view (Request $request, $model) {
        $m = get_class($model);
        $relationships = [];
        $included = [];
        $all_relations = array_merge($m::$RELATIONSHIPS['belongs_to'], $m::$RELATIONSHIPS['has_many'], $m::$RELATIONSHIPS['belongs_to_and_has_many']);
        // refresh relationships from db
        $keys = array_keys($all_relations);
        $model->load($keys);
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

        $link = null;
        $link_segments = $request->segments();
        if ((count($link_segments) > 0) && is_numeric($link_segments[count($link_segments) - 1])) {
            $link = $request->url();
        } else {
            $link = $request->url() . '/' . $model->id;
        }
        $value = [
            'data' => [
                'type' => self::TYPE,
                'id' => $model->id,
                'attributes' => $model,
                'links' => [
                    'self' => $link
                ],
                'relationships' => $relationships
            ],
            'included' => $included
        ];
        return $value;
    }

    private function get_relation_item_array (Request $request, $description, $relation) {
        $relation_item = [];

        $link = $request->root() . config('names.path.' . $description['type']). '/' . $relation->id;
        $relation_item['links'] = [
            'self' => $link
        ];
        $relation_item['attributes'] = [
            'id' => $relation->id,
            'type' => $description['type'],
            'attributes' => $relation
        ];

        $inclusion_item = [];
        $inclusion_item['type'] = $description['type'];
        $inclusion_item['id'] = $relation->id;
        $inclusion_item['attributes'] = $relation;
        $inclusion_item['links'] = ['self' => $link];
        return [
            'relation_item' => $relation_item,
            'inclusion_item' => $inclusion_item
        ];
    }

    private function save_model (Request $request, $model, $fullOverwrite = false) {
        $m = get_class($model);
        if ($fullOverwrite) {
            $attributes = $model->getAttributes();
            foreach ($attributes as $key => $value) {
                $attributes[$key] = null;
            }
            $model->fill($attributes);
        }
        $model->fill($request->input());
        if (array_key_exists('user', $m::$RELATIONSHIPS['belongs_to'])) {
            $model->user()->associate($request->user());
        }
        $save_relations = function ($relation, $key) use ($model, $request, $fullOverwrite) {
            if ($request->has($relation)) {
                $className = 'App\\' . config('names.class.' . $key);
                $id = $request->input($relation);
                if (is_numeric($id)) { // belongs to relationship
                    $model->{$relation}()->associate($id);
                } else if (is_array($id)) {
                    // delete all items that were in relationship before, but not after the update
                    // get class name
                    $key = array_search($relation, config('names.plural'));
                    // get instances of the references items
                    $links = $className::find($id);
                    $new_ids = $id;
                    
                    $relations = $model->{$relation};
                    $old_ids = [];
                    foreach($relations as $rel) {
                        array_push($old_ids, $rel->id);
                    }
                    $in_old_but_not_in_new = array_diff($old_ids, $new_ids);
                    $className::destroy($in_old_but_not_in_new);

                    $model->{$relation}()->saveMany($links);
                }
            } else {
                // request has no definition of this relation
                if ($fullOverwrite) {
                    $class = get_class($model->{$relation}());
                    if ('Illuminate\Database\Eloquent\Relations\BelongsTo' === $class) {
                        $value = ['error' => 'belongs to relation is missing'];
                        $this->respond('unprocessable', $value);
                    } else if ('Illuminate\Database\Eloquent\Relations\HasMany' === $class) {
                        $instances = $model->{$relation};
                        $ids = [];
                        $instances->each(function ($item) use ($ids) {
                            array_push($ids, $item->id);
                        });
                        
                        $className::destroy($ids);
                    }
                }
            }
        };
        $relationships_belongs_to = array_keys($m::$RELATIONSHIPS['belongs_to']);
        array_walk($relationships_belongs_to, $save_relations);
        if ($model->id === null) {
            try {
                $model->save();
            } catch (Exception $e) {
                $value = [
                    'error' => 'Bad Request',
                    'details' => 'Could not do this because the given model was invalid.'
                ];
                $this->respond('not_valid', $value);
            }
        }
        $relationships_has_many = array_keys($m::$RELATIONSHIPS['has_many']);
        array_walk($relationships_has_many, $save_relations);
        $relationships_belongs_to_and_has_many = array_keys($m::$RELATIONSHIPS['belongs_to_and_has_many']);
        array_walk(
            $relationships_belongs_to_and_has_many,
            function ($relation, $relation_name) use ($model, $request) {
                $relation_array = ($request->input($relation) === null) ? [] : $request->input($relation);
                if ($model->{$relation}()) {
                    try {
                        $model->{$relation}()->sync($relation_array);
                    } catch (Exception $e) {
                        $value = ['error' => 'heise'];
                        return $this->respond('not_valid', $value);
                    }
                }
            }
        );
        $model->save();
    }

    private function get_validation_exception_values (ValidationException $e) {
        $details = [];
        foreach ($e->getResponse()->getData() as $key => $message) {
            array_push($details, $message);
        }
        $value = [
            'error' => 'The data was not correct',
            'details' => $details
        ];
        return $value;
    }

    public function all (Request $request) {
        $m = self::MODEL;
        $models = $m::all();
        $user = $request->user();
        $items = [];
        foreach ($models as $model) {
            if ($user->can('view', $model)) {
                array_push($items, $model);
            }
        }
        $values = [];
        foreach ($items as $item) {
            $value = $this->get_view($request, $item);
            array_push($values, $value);
        }
        return $this->respond('done', $values);
    }

    public function view (Request $request, $id) {
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)){
            $value = ['error' => 'Model not found'];
            return $this->respond('not_found', $value);
        }
        $value = $this->get_view($request, $model);
        return $this->respond('done', $value);
    }

    public function create (Request $request) {
        $m = self::MODEL;
        try {
            $this->validate($request, $m::VALIDATION($request));
        } catch (ValidationException $e) {
            $value = $this->get_validation_exception_values($e);
            return $this->respond('unprocessable', $value);
        }

        $model = new $m;
        $this->save_model($request, $model);

        // handle output
        $model->makeHidden('user_id')->toArray();
        $value = $this->get_view($request, $model);
        return $this->respond('created', $value);
    }

    public function patch (Request $request, $id) {
        $m = self::MODEL;
        try {
            $this->validate($request, $m::VALIDATION($request, $id));
        } catch (ValidationException $e) {
            $value = $this->get_validation_exception_values($e);
            return $this->respond('created', $value);
        }
        $model = $m::find($id);
        if (is_null($model)){
            return $this->respond('not_found');
        }
        $this->save_model($request, $model, false);

        // handle output
        $model->makeHidden('user_id')->toArray();
        $value = $this->get_view($request, $model);
        return $this->respond('done', $value);
    }

    public function update (Request $request, $id) {
        $m = self::MODEL;
        try {
            $this->validate($request, $m::VALIDATION($request, $id));
        } catch (ValidationException $e) {
            $value = $this->get_validation_exception_values($e);
            return $this->respond('created', $value);
        }
        $model = $m::find($id);
        if (is_null($model)){
            return $this->respond('not_found');
        }
        $this->save_model($request, $model, true);

        // handle output
        $model->makeHidden('user_id')->toArray();
        $value = $this->get_view($request, $model);
        return $this->respond('done', $value);
    }

    public function delete ($id) {
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