<?php
include 'alcuin/system/main.php';

$config_file = 'configuration.yaml';

echo '<!DOCTYPE html>
            <head>
                <title>Alcuin &mdash; DB and REST for the layy ones</title>
                <link rel="stylesheet" href="alcuin/gui/css/bootstrap.min.css">
            </head>
            <body>
                <div class="container"><h1>Alcuin</h1><hr />
                    <ul class="list-unstyled">';

alcuin($config_file);
echo '</body>';
?>
