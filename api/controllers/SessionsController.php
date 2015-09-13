<?php
require ('Controller.php');

class SessionsController extends Controller {

    private static $TABLE_NAME = 'users';
    private static $TOKEN_COLUMN_NAME = 'token';
    private static $TOKEN_LAST_UPDATE_COLUMN_NAME = 'token_last_updated';

    protected function get_action($request) {
        return $this->create_error('GET is not supported for sessions. Use POST to create a session.');
    }

    protected function put_action($request) {
        return $this->create_error('PUT is not supported for sessions. Use POST to create a session.');
    }

    /**
     *  If a new session is requested, then first look into the DB whether a valid session already exists
     *  If so, then update the return the value of the session
     *  If not, then create a new one and return that
    */
    protected function post_action($request) {
        $props = new stdClass();
        $token_base = '';
        foreach ($request->getParameters() as $key => $value) {
            $props->{$key} = $value;
            $token_base .= $key . '=' . $value . ';';
        }
        $users = ORM::retrieve(self::$TABLE_NAME, $props);
        $session = null;
        if (sizeof($users) === 1) {
            $user_session = $users[0];
            if ($user_session->{self::$TOKEN_COLUMN_NAME} === NULL) {
                // update the token
                $token_base .= microtime();
                $token_length = strlen($token_base);
                foreach (base::getSalt() as $index) {
                    if ($index < $token_length) {
                        $token_base .= substr($token_base, $index, 1);
                    }
                }
                $user_session->{self::$TOKEN_COLUMN_NAME} = sha1($token_base);
            }
            // update the user token
            $user_ession->{self::$TOKEN_LAST_UPDATE_COLUMN_NAME} = Utils::getNow();
            ORM::save($user_session);

            // trim for return
            $session = new stdClass();
            $session->{self::$TOKEN_COLUMN_NAME} = $user_session->{self::$TOKEN_COLUMN_NAME};
            $session->{self::$TOKEN_LAST_UPDATE_COLUMN_NAME} = $user_session->{self::$TOKEN_LAST_UPDATE_COLUMN_NAME};
        } else {
            throw new Exception('Could not assign the session to the given request.');
        }

        return $session;
    }

    protected function delete_action($request) {
        echo 'asf';
    }

}

?>