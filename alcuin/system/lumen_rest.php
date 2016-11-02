<?php

    function get_authentication ($configuration) {
        $name = $configuration->architecture->use_for_auth;
        $model = $configuration->architecture->models->{$name};
        $id_property = null;
        $password_property = null;
        foreach ($model->properties->list as $property_name => $property) {
            if ($id_property && $password_property) {
                break;
            }
            if ($property->use_as_id) {
                $id_property = $property_name;
            }
            if ($property->use_as_password) {
                $password_property = $property_name;
            }
        }
        $result = [
            'name' => $name,
            'plural' => get_model_plural_name($name, $configuration->architecture->models->{$name}),
            'class_name' => ucfirst($name),
            'model' => $configuration->architecture->models->{$name},
            'id_property' => $id_property,
            'password_property' => $password_property
        ];
        // echo '<pre>';
        // print_r($result);
        // echo '</pre>';
        return $result;
    }
    
    function create_lumen_config ($configuration) {
        $dir_name = $configuration->web->service_dir;
        next_item('Creating app file');
        $config = ['names' => ['path' => [], 'class' => [], 'plural' => []]];
        foreach ($configuration->architecture->models as $model_name => $model) {
            $config['names']['path'][$model_name] = '/' . $model->name_plural;
            $config['names']['plural'][$model_name] = get_model_plural_name($model_name, $model);
            $config['names']['class'][$model_name] = ucfirst($model_name);

             copy_with_data('./alcuin/scaffold/config/app.php.scaffold', './' . $dir_name . '/bootstrap/app.php', [
                'config' => $config
            ]);
        }
        success();

        next_item('Creating .env file');
        copy_with_data('./alcuin/scaffold/config/.env.scaffold', './'. $dir_name . '/.env', [
            'api_key' => $configuration->web->api_key,
            'db_host' => $configuration->db->host,
            'db_port' => $configuration->db->port,
            'db_name' => $configuration->db->name,
            'db_username' => $configuration->db->user,
            'db_userpassword' => $configuration->db->password
        ]);
        success();

        next_item('Creating routes');
        $authentication = get_authentication($configuration);
        copy_with_data('./alcuin/scaffold/config/routes.php.scaffold', './'. $dir_name . '/app/Http/routes.php', [
            'authentification' => $authentication['name'],
            'authentification_plural' => $authentication['plural'],
            'authentification_uc' => $authentication['class_name']
        ]);
        success();

    }

    function create_lumen_middleware ($configuration) {
        $dir_name = $configuration->web->service_dir;
        next_item('Creating Authentification Middleware');
        copy_with_data(
            './alcuin/scaffold/middleware/Authenticate.php.scaffold',
            './'. $dir_name . '/app/Http/Middleware/Authenticate.php');

        $authentication = get_authentication($configuration);
        copy_with_data(
            './alcuin/scaffold/middleware/AuthServiceProvider.php.scaffold',
            './'. $dir_name . '/app/Providers/AuthServiceProvider.php', [
                'dir_name' => $dir_name,
                'authentication' => $authentication['name'],
                'authentication_class_name' => $authentication['class_name'],
                'id_property' => $authentication['id_property']
            ]);
        success();

        next_item('Creating Authorization Middleware');

        $usages = new stdClass();
        $replacements = [];
        foreach ($configuration->architecture->models as $model_name => $model) {
            array_push($replacements, [
                'class_name' => ucfirst($model_name)
            ]);
        }
        $usages->template = "use App\\{{class_name}} as {{class_name}};\n";
        $usages->replacements = $replacements;

        copy_with_data(
            './alcuin/scaffold/middleware/Authorize.php.scaffold',
            './'. $dir_name . '/app/Http/Middleware/Authorize.php', [
                'authentication' => $authentication['name'],
                'authentication_class_name' => $authentication['class_name'],
                'usages' => $usages
            ]);
        copy_with_data(
            './alcuin/scaffold/middleware/AuthServiceProvider.php.scaffold',
            './'. $dir_name . '/app/Providers/AuthServiceProvider.php', [
                'url_root' => $configuration->web->url_root,
                'authentication' => $authentication['name'],
                'authentication_class_name' => $authentication['class_name'],
                'id_property' => $authentication['id_property']
            ]);
        
        $usages = new stdClass();
        $gates = new stdClass();
        $replacements = [];
        foreach ($configuration->architecture->models as $model_name => $model) {
            array_push($replacements, [
                'class_name' => ucfirst($model_name)
            ]);
        }

        $usages->template = 'use App\\{{class_name}};
use App\\Policies\\{{class_name}}Policy;
';
        $usages->replacements = $replacements;

        $gates->template = '
        Gate::policy({{class_name}}::class, {{class_name}}Policy::class);';
        $gates->replacements = $replacements;
        copy_with_data(
            './alcuin/scaffold/middleware/AuthorizationServiceProvider.php.scaffold',
            './'. $dir_name . '/app/Providers/AuthorizationServiceProvider.php', [
                'usages' => $usages,
                'gates' => $gates
            ]);
        success();

        $usages = new stdClass();
        $observers = new stdClass();
        $replacements = [];
        foreach ($configuration->architecture->models as $model_name => $model) {
            array_push($replacements, [
                'class_name' => ucfirst($model_name)
            ]);
        }

        $usages->template = 'use App\\{{class_name}};
use App\\Observers\\{{class_name}}Observer;
';
        $usages->replacements = $replacements;

        $observers->template = '
        {{class_name}}::observe({{class_name}}Observer::class);';
        $observers->replacements = $replacements;
        copy_with_data(
            './alcuin/scaffold/middleware/AppServiceProvider.php.scaffold',
            './'. $dir_name . '/app/Providers/AppServiceProvider.php', [
                'usages' => $usages,
                'observers' => $observers
            ]);
        success();
    }

    function create_lumen_models ($configuration) {
        $authentication = get_authentication($configuration);
        foreach ($configuration->architecture->models as $model_name => $model) {
            next_item('Creating model for <code>' . $model_name . '</code>');
            $model_class_name = ucfirst($model_name);
            $plural_name = get_model_plural_name($model_name, $model);
            $authentication = get_authentication($configuration);


            $usages = '';
            $implements = '';
            $inner_usages = '';
            if ($model_name === $authentication['name']) {
                $usages = '
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
';
                $implements = 'implements AuthenticatableContract, AuthorizableContract';
                $inner_usages = 'use Authenticatable, Authorizable;';
            }

            $hidden = ['pivot'];
            $properties = [];
            $hidden = array_merge($hidden, $properties);

            $validation = [];
            $property_permissions = [];
            $permissions = [];
            $permissions['create'] = $model->properties->permissions->create ? : [];
            $permissions['read'] = $model->properties->permissions->read ? : [];
            $permissions['update'] = $model->properties->permissions->update ? : [];
            $property_permissions = [];


            foreach ($model->properties->list as $property_name => $property) {
                // property names
                array_push($properties, $property_name);
                // set property permissions
                // copy general permissions
                foreach ($permissions as $access => $value) {
                    $property_permissions[$property_name][$access] = $value;
                }
                if (isset($property->permissions)) {
                    foreach ($property->permissions as $permissions_name => $permission) {
                        $property_permissions[$property_name][$permissions_name] = $permission;
                    }
                }
                // validation
                $validation_array = [];
                switch ($property->type) {
                    case 'email':
                        array_push($validation_array, 'email');
                        break;
                    case 'float':
                        array_push($validation_array, 'numeric');
                        break;
                    case 'int':
                        array_push($validation_array, 'integer');
                        break;
                    case 'hash':
                        array_push($validation_array, 'max:64');
                        array_push($validation_array, 'min:64');
                        array_push($validation_array, 'alpha_num');
                        break;
                    case 'datetime':
                        array_push($validation_array, 'date_format:Y-m-d H:i:s');
                        break;
                    case 'bool':
                        array_push($validation_array, 'boolean');
                        break;
                    case 'geo':
                        break;
                    case 'timezone':
                        array_push($validation_array, 'timezone');
                        break;
                    case 'enum':
                        $allowed_values = $property->allowed_values;
                        array_push($validation_array, 'in:' . implode(',', $allowed_values));
                        break;
                    default:
                        break;
                }
                if ($property->max_length) {
                    array_push($validation_array, 'max:' . $property->max_length);
                }
                if ($property->null_allowed) {
                    array_push($validation_array, 'nullable');
                }
                if ($property->use_as_id) {
                    array_push($validation_array, 'required');
                    array_push($validation_array, 'unique:' . $authentication['plural'] . ',' . $property_name);
                }

                $validation[$property_name] = implode('|', $validation_array);
            }

            $validation_authentication = '';
            if ($model_name === $authentication['name']) {
                $validation_authentication = 'if (!is_null($model)) {
            $validation[\'' . $authentication['id_property'] . '\'] .= ",{$model->' . $authentication['id_property'] . '},email";
        }';
            }


            // collect relationships
            $relationships = [
                BELONGS_TO => [],
                HAS_MANY => [],
                BELONGS_TO_AND_HAS_MANY => []
            ];

            $relationship_permissions = [];

            $belongs_to_desc = [];
            $has_many_desc = [];
            $belongs_to_and_has_many_desc = [];

            foreach ($model->relations as $relation_name => $relation) {
                $relationships[$relation->type][$relation_name] = [
                    'type' => $relation->model
                ];
                $relationship_permissions[$relation_name] = $relation->permissions;
                switch ($relation->type) {
                    case BELONGS_TO:
                        array_push($belongs_to_desc, [
                            'relation_name' => $relation_name,
                            'relation_class_name' => ucfirst($relation->model)
                        ]);
                        break;
                    case HAS_MANY:
                        array_push($has_many_desc, [
                            'relation_name' => $relation_name,
                            'relation_class_name' => ucfirst($relation->model)
                        ]);
                        break;
                    case BELONGS_TO_AND_HAS_MANY:
                        array_push($belongs_to_and_has_many_desc, [
                            'relation_name' => $relation_name,
                            'relation_class_name' => ucfirst($relation->model),
                            'via_table' => $relation->via_table,
                            'type' => $model_name,
                            'relation_type' => $relation->model
                        ]);
                        break;
                    default:
                        break;
                }
            }

            $belongs_to_functions = new stdClass();
            $belongs_to_functions->template = '
    public function {{relation_name}} () {
        return $this->belongsTo(\'App\{{relation_class_name}}\');
    }';
            $belongs_to_functions->replacements = $belongs_to_desc;

            $has_many_functions = new stdClass();
            $has_many_functions->template = '
    public function {{relation_name}} () {
        return $this->hasMany(\'App\{{relation_class_name}}\');
    }';
            $has_many_functions->replacements = $has_many_desc;

            $belongs_to_and_has_many_functions = new stdClass();
            $belongs_to_and_has_many_functions->template = '
    public function {{relation_name}} () {
        return $this->belongsToMany(\'App\{{relation_class_name}}\', \'{{via_table}}\', \'{{type}}_id\', \'{{relation_type}}_id\');
    }';
            $belongs_to_and_has_many_functions->replacements = $belongs_to_and_has_many_desc;
            $replacement_description = [
                'usages' => $usages,
                'implements' => $implements,
                'inner_usages' => $inner_usages,
                'class_name' => ucfirst($model_name),
                'type' => $model_name,
                'hidden' => $hidden,
                'validation' => $validation,
                'validation_authentication' => $validation_authentication,
                'properties' => $properties,
                'property_permissions' => $property_permissions,
                'relationships' => $relationships,
                'relationship_permissions' => $relationship_permissions,
                'has_many_functions' => $has_many_functions,
                'belongs_to_and_has_many_functions' => $belongs_to_and_has_many_functions,
            ];
            if (empty($belongs_to_desc)) {
                $replacement_description['belongs_to_functions'] = '';
            } else {
                $replacement_description['belongs_to_functions'] = $belongs_to_functions;
            }
            if (empty($has_many_desc)) {
                $replacement_description['has_many_functions'] = '';
            } else {
                $replacement_description['has_many_functions'] = $has_many_functions;
            }
            if (empty($belongs_to_and_has_many_desc)) {
                $replacement_description['belongs_to_and_has_many_functions'] = '';
            } else {
                $replacement_description['belongs_to_and_has_many_functions'] = $belongs_to_and_has_many_functions;
            }

            copy_with_data(
                './alcuin/scaffold/models/Model.php.scaffold',
                './' . $configuration->web->service_dir . '/app/' . $model_class_name . '.php',
                $replacement_description
            );
            success();
        }
    }

    function create_lumen_controllers ($configuration) {
        next_item('Creating Base Controller');
        copy_with_data(
            './alcuin/scaffold/controllers/Controller.php.scaffold',
            './' . $configuration->web->service_dir . '/app/Http/Controllers/Controller.php',
            [
                'salt' => $configuration->salt,
                'pepper' => $configuration->pepper
            ]);
        success();


        next_item('Creating REST Implementation Mixin');
        $authentication = get_authentication($configuration);
        $authorization_name = $configuration->architecture->use_for_permission;
        $a_a_relation_name = null;
        foreach($authentication['model']->relations as $relation_name => $relation) {
            if ($configuration->architecture->use_for_permission === $relation->model) {
                $a_a_relation_name = $relation_name;
                break;
            }
        }
        $authorization_id_name = null;
        foreach ($configuration->architecture->models->{$authorization_name}->properties->list as $property_name => $property) {
            if ($property->use_as_id) {
                $authorization_id_name = $property_name;
                break;
            }
        }
        copy_with_data(
            './alcuin/scaffold/controllers/RESTActions.php.scaffold',
            './' . $configuration->web->service_dir . '/app/Http/Controllers/RESTActions.php',
            [
                'authentication_class_name' => $authentication['class_name'],
                'authentication_name' => $authentication['name'],
                'authentication_plural_name' => $authentication['plural'],
                'authorization_name' => $authorization_name,
                'authentification_authorization_relation_name' => $a_a_relation_name,
                'authorization_id_name' => $authorization_id_name
            ]);
        success();

        next_item('Creating Token Controller');
        copy_with_data(
            './alcuin/scaffold/controllers/TokenController.php.scaffold',
            './' . $configuration->web->service_dir . '/app/Http/Controllers/TokenController.php',
            [
                'authentication_class_name' => $authentication['class_name'],
                'authentication_id_property' => $authentication['id_property'],
                'authentication_password_property' => $authentication['password_property'],
                'authentication_name' => $authentication['name']
            ]);
        success();


        foreach ($configuration->architecture->models as $model_name => $model) {
            next_item('Creating Controller for <code>' . $model_name . '</code>');


            $replacements = [
                'authentication_usages' => '',
                'authentication_method_override' => '',
                'class_name' =>  ucfirst($model_name),
                'name' => $model_name
            ];

            if ($model_name === $authentication['name']) {
                $replacements['authentication_usages'] = 'use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\User;
';
                $replacements['authentication_method_override'] = '
    public function create (Request $request) {
        $' . $authentication['id_property']. ' = $request->has(\'' . $authentication['id_property']. '\') ? $request->input(\'' . $authentication['id_property']. '\') : null;
        $' . $authentication['password_property'] . ' = $request->has(\'' . $authentication['password_property'] . '\') ? $request->input(\'' . $authentication['password_property'] . '\') : null;
        $m = self::MODEL;
        try {
            $this->validate($request, $m::VALIDATION($request));
        } catch (ValidationException $e) {
            $details = [];
            foreach ($e->getResponse()->getData() as $key => $message) {
                array_push($details, $message);
            }
            $value = [
                \'error\' => \'The data was not correct\',
                \'details\' => $details
            ];
            return response()->json($value, 422);
        }

        $user = new User;
        $this->save_model($request, $user);
        $user->' . $authentication['id_property']. ' = $' . $authentication['id_property']. ';
        $user->' . $authentication['password_property'] . ' = hash(\'sha256\', self::spice($' . $authentication['password_property'] . '));
        $user->save();

        $value = [
            \'data\' => [
                \'id\' => $user->id,
                \'' . $authentication['id_property']. '\' => $user->' . $authentication['id_property'] . ',
            ],
            \'links\' => [
                \'self\' => $request->fullUrl() . \'/\' . $user->id
            ],
            \'relationships\' => [
                \'token\' => [
                ]
            ]
        ];
        return response()->json($value, 201);
    }';
            }
            copy_with_data(
                './alcuin/scaffold/controllers/ModelController.php.scaffold',
                './' . $configuration->web->service_dir . '/app/Http/Controllers/' . ucfirst($model_name) . 'Controller.php',
                $replacements);
            success();
        }
    }

    function create_lumen_policies ($configuration) {
        foreach ($configuration->architecture->models as $model_name => $model) {

            next_item('Creating policy for <code>' . $model_name . '</code>');
            $model_usage = 'use App\\' . ucfirst($model_name) . ';';
            $model_name_param = $model_name;
            $authentication = get_authentication($configuration);
            if ($model_name === $authentication['name']) {
                $model_usage = '';
                $model_name_param .= '_param';
            }

            $authorization_name = $configuration->architecture->use_for_permission;
            $replacements = [
                'authentication_class_name' => $authentication['class_name'],
                'authentication_name' => $authentication['name'],
                'authorization_class_name' => ucfirst($authorization_name),
                'model_usage' => $model_usage,
                'model_name' => $model_name_param,
                'model_class_name' => ucfirst($model_name),
                'create_function_body' => get_function_body(
                        $model_name,
                        $model,
                        $authentication['name'],
                        $model_name_param,
                        'create',
                        $configuration),
                'view_function_body' => get_function_body(
                        $model_name,
                        $model,
                        $authentication['name'],
                        $model_name_param,
                        'read',
                        $configuration),
                'update_function_body' => get_function_body(
                        $model_name,
                        $model,
                        $authentication['name'],
                        $model_name_param,
                        'update',
                        $configuration),
                'delete_function_body' => get_function_body(
                        $model_name,
                        $model,
                        $authentication['name'],
                        $model_name_param,
                        'delete',
                        $configuration)
            ];
            copy_with_data(
                './alcuin/scaffold/policies/ModelPolicy.php.scaffold',
                './' . $configuration->web->service_dir . '/app/Policies/' . ucfirst($model_name) . 'Policy.php',
                $replacements);
            success();    
        }
    }


    function create_lumen_observers ($configuration) {
        next_item('Creating Model Observer');
        copy_with_data(
            './alcuin/scaffold/observers/ModelObserver.php.scaffold',
            './' . $configuration->web->service_dir . '/app/Observers/ModelObserver.php',
            []);
        success();
        foreach ($configuration->architecture->models as $model_name => $model) {
            next_item('Creating observer for <code>' . $model_name . '</code>');
            copy_with_data(
            './alcuin/scaffold/observers/Observer.php.scaffold',
            './' . $configuration->web->service_dir . '/app/Observers/' . ucfirst($model_name) . 'Observer.php',
            [
                'class_name' => ucfirst($model_name)
            ]);
            success();
        }
    }

    function get_function_body ($model_name, $model, $authentication_name, $parameter_name, $action, $configuration) {
        $body = null;
        $permissions = $model->permissions->{$action};
        if (in_array('all', $permissions)) {
            $body = '// all permissions
        return true;
';
        } else if (in_array('none', $permissions)) {
            $body = '// none permissions
        return false;
';
        } else {
            $authentication = get_authentication($configuration);
            $authorization_name = $configuration->architecture->use_for_permission;
            if (in_array('self', $permissions)) {
                if ($model_name === $authentication_name)  {
                    $body = '// self permissions
        $isAllowed = $' . $authentication_name . '->id === $' . $parameter_name . '->id;';
                } else {
                    $relation_type = null;
                    $relation_name = null;
                    $relation_model = null;
                    $a_model_relation_name = null;
                    foreach ($model->relations as $relation_name_iter => $relation) {
                        if ($relation->model === $authentication_name) {
                            $relation_type = $relation->type;
                            $relation_model = $relation->model;
                            $relation_name = $relation_name_iter;

                            foreach ($authentication['model']->relations as $m_a_relation_name => $m_a_relation) {
                                if ($m_a_relation->model === $model_name &&
                                    $m_a_relation->type === BELONGS_TO) {
                                    $a_model_relation_name = $m_a_relation_name;
                                }
                            }
                            break;
                        }
                    }

                    $a_a_relation_name = null;
                    foreach($authentication['model']->relations as $a_relation_name => $relation) {
                        if ($configuration->architecture->use_for_permission === $relation->model) {
                            $a_a_relation_name = $a_relation_name;
                            break;
                        }
                    }
                    $authorization_id_name = null;
                    foreach ($configuration->architecture->models->{$authorization_name}->properties->list as $property_name => $property) {
                        if ($property->use_as_id) {
                            $authorization_id_name = $property_name;
                            break;
                        }
                    }
                    if ($relation_type === BELONGS_TO) {
                        $body = '// self permission type: belongs to
        $isAllowed = $' . $parameter_name . '->' . $relation_name . '_id === $' . $authentication_name . '->id;
';
                    } else if ($relation_type === HAS_MANY) {
                        $body = '// self permission type: has many
        $isAllowed = $' . $authentication_name . '->' . $a_model_relation_name . '_id === $' . $parameter_name . '->id;
';

                    } else if ($relation_type === BELONGS_TO_AND_HAS_MANY) {
                        $body = '// self permission type: belongs to and has many
        $rel_' . $authentication['name'] . '_ids = [];
        foreach ($model->' . $authentication['plural'] . ' as $rel_' . $authentication['name'] . ') {
            array_push($rel_' . $authentication['name'] . '_ids, $rel_' . $authentication['name'] . '->id);
        }
        $isAllowed = in_array($' . $authentication_name . '->id, $rel_' . $authentication['name'] . '_ids);
';
                    }
                }
            }
            // handle role based permissions
            if (count(array_diff($permissions, ['all', 'none', 'self'])) > 0) {
                $perms = array_diff($permissions, ['all', 'none', 'self']);
                $a_a_relation_name = null;
                foreach($authentication['model']->relations as $relation_name => $relation) {
                    if ($configuration->architecture->use_for_permission === $relation->model) {
                        $a_a_relation_name = $relation_name;
                        break;
                    }
                }
                $authorization_id_name = null;
                foreach ($configuration->architecture->models->{$authorization_name}->properties->list as $property_name => $property) {
                    if ($property->use_as_id) {
                        $authorization_id_name = $property_name;
                        break;
                    }
                }

                $role_based = '
            foreach ($' . $authentication_name . '->' . $a_a_relation_name . ' as $' . $authorization_name . ') {
                if (in_array($' . $authorization_name .'->' . $authorization_id_name . ', ' . array_to_string($perms) . ')) {
                    $isAllowed = true;
                    break;
                }
            }';
            }
            $needs_closing = false;
            if ($body) {
                $needs_closing = true;
                $body .= '
        if (!$isAllowed) {';
            } else {
                $body .= '
            $isAllowed = false;
        ';
            }
            $body .= $role_based;
            if ($needs_closing) {
                $body .= '
        }';
            }

            $body .= '
        return $isAllowed;';
        }

        return $body;
    }

?>