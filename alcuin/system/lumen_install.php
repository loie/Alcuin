<?php
    function install_lumen ($configuration) {
        $name = $configuration->web->service_dir;
        assert($name !== null);

        next_item('Installing lumen in directory <code>' . $name . '</code>');
        copy_with_data('./alcuin/scaffold/install/prepare.sh.scaffold', './prepare.sh', [
            'service_dir' => $name
        ]);
        // execute script
        chmod('./prepare.sh', 0755);
        echo '<pre><code>';
        $feedback = shell_exec('./prepare.sh');
        echo $feedback;
        echo '</code></pre>';

        unlink('./prepare.sh');
        success();
    }
?>