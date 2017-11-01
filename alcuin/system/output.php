<?php

    function next_item ($text = null) {
        echo '<li>' . $text . '&hellip; ';
    }

    function handle_next ($next_text = null) {
        if ($next_text !== null) {
            next_item($next_text);
        }
    }

    function success ($next_text = null) {
        echo '<strong class="text-success">Success</strong></li>';
        handle_next($next_text);
    }

    function skipped ($next_text = null) {
         echo '<strong class="text-info">Skipped</strong></li>';
         handle_next($next_text);
    }

    function open_sub ($message = null) {
        if ($message !== null) {
            echo $message . '&hellip;';
        }
        echo '<ul>';
    }

    function close_sub ($level = 1) {
        for ($i = 0; $i < $level; $i += 1) {
            echo '</li></ul>';
        }
    }

    function error ($error, $details = null) {
        echo '<strong class="text-danger">Failed</strong></li>';
        echo '</ul>';
        echo '<div class="alert alert-danger" role="alert"><strong>An error occured:</strong> ' . $error;
        if ($details !== null) {
            echo '<hr /><strong>Error message:</strong>';
            echo '<pre>' . $details . '</pre>';
        }
        echo '</div>';
        die();
    }

?>