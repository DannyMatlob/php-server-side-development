<?php
    function sanitizeSQL($conn, $var) {
        return htmlentities($conn->real_escape_string($var));
    }

    function sqlError() {
        die("<br>Something went wrong<br>");
    }

    function createForm() {
        echo <<<_END
            <html><head><title>PHP Form Upload</title></head><body>
            <form method='post' action='hw4.php' enctype='multipart/form-data'>
            Input your Email: <input type='text' name='email' size='30'>
            <br><br>
            Select Text File: <input type='file' name='filename' size='10'>
            <br>
            <input type='submit' value='Upload'>
            </form>
            _END;
    }
?>