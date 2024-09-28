<?php
    const ONE_MINUTE_IN_SECONDS = 60 * 60;
    function sanitize($conn, $var) {
        return htmlentities($conn->real_escape_string($var));
    }

    function sqlError($conn) {
        die("<br>Something went wrong<br>");
    }

    function destroySessionAndData() {
        $_SESSION = array();
        setcookie(session_name(), '', time() - ONE_MINUTE_IN_SECONDS, '/');
        session_destroy();
    }
?>