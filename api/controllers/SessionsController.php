<?php
class SessionsController extends Controller {

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
        $props = [];
        $token_base = '';
        foreach ($request->getParameters() as $key => $value) {
            $props[$key] = $value;
            $token_base .= $key . '=' . $value . ';';
        }
        $sessions = ORM::retrieve('sessions', $props);
        $session = null;
        if (sizeof($sessions) === 1) {
            $session = $sessions[0];
            if ($session->token === NULL) {
                // update the token
                $token_base .= microtime();
                $token_length = strlen($token_base);
                foreach (base::getSalt() as $index) {
                    if ($index < $token_length) {
                        $token_base .= substr($token_base, $index, 1);
                    }
                }
                $session->token = sha1($token_base);
            }
            // update the user token
            $session->token_last_updated = Utils::getNow();
            ORM::save($session);
            return $session;
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