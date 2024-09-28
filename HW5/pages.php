<?php
    require_once("hw5.php");
    function loginSignUpPage() {
        echo <<<_END
            <html><head><title>Login and Signup</title></head><body>
            
            <h3>Login</h3>
            <form method='post' action='hw5.php' enctype='multipart/form-data'>
            Input your Username: <input type='text' name='loginun' size='30'>
            <br>
            Input your Password: <input type='text' name='loginpw' size='30'>
            <br>
            <input type='submit' value='Login'>
            </form>

            <h3>Sign Up</h3>
            <form method='post' action='hw5.php' enctype='multipart/form-data'>
            Display Name: <input type='text' name='signupName' size='30'>
            <br>
            Username: <input type='text' name='signupun' size='30'>
            <br>
            Password: <input type='text' name='signuppw' size='30'>
            <br>
            <input type='submit' value='Sign Up'>
            </form>
        _END;
    }

    function homePage() {
        echo <<<_END
            <form method='post' action='hw5.php' enctype='multipart/form-data'>
            <input type='submit' value='Logout' name='logout'>
            </form>
            <h2> Make a new post </h2>
            <form method='post' action='hw5.php' enctype='multipart/form-data'>
            Title: <br>
            <input type='text' name='title' size='30'>
            <br>
            Content: <br>
            <textarea name='content' rows='10' cols='50'></textarea>
            <br>
            <input type='submit' value='Make Post'>
            </form>
            <h2> Your Posts </h2>
        _END;
    }

?>