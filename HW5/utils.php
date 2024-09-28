<?php
    function sanitize($conn, $var) {
        return htmlentities($conn->real_escape_string($var));
    }

    function sqlError() {
        die("<br>Something went wrong<br>");
    }
?>