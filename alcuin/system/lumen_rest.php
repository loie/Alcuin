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
        return [
            'name' => $name,
            'plural' => get_model_plural_name($name, $configuration->architecture->models->{$name}),
            'class_name' => ucfirst($name),
            'model' => $configuration->architecture->model->{$name},
            'id_property' => $id_property,
            'password_property' => $password_property
        ];
    }
    
    function create_lumen_config ($configuration) {
        $dir_name = $configuration->web->service_dir;
        next_item('Creating app file');
        $config = ['names' => ['path' => [], 'class' => [], 'plural' => []]];
        foreach ($configuration->architecture->models as $model_name => $model) {
            $config['names']['path'][$model_name] = '/' . $model->plural_name;
            $config['names']['plural'][$model_name] = get_model_plural_name($model_name, $model);
            $config['names']['class'][$model_name] = ucfirst($model_name);

             copy_with_data('./alcuin/scaffold/config/app.php.scaffold', './' . $dir_name . '/bootstrap/app.php', [
                'config' => $config
            ]);
        }
        success();

        next_item('Creating .env file');
        copy_with_data('./alcuin/scaffold/config/.env.scaffold', './'. $dir_name . '.env', [
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
        $replacements['class_name'] = [];
        foreach ($configuration->architecture->models as $model_name => $model) {
            array_push($replacements['class_name'], $model_name);
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
        success();
    }

    function create_lumen_policies ($configuration) {
        next_item('Creating');
        success();
    }

    function create_lumen_models ($configuration) {
        next_item('Creating');
        success();
    }

    function create_lumen_controllers ($configuration) {
        next_item('Creating');
        success();
    }


    function create_lumen_observers ($configuration) {
        next_item('Creating');
        success();
    }

?>