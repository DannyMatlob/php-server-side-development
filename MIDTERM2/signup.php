<?php
    session_start();
    session_regenerate_id();
    require_once("login.php");
    require_once("utils.php");

    $status = "";

    try {
        $conn = new mysqli($hn, $un, $pw, $db);
    } catch (Exception $e) {
        sqlError($conn);
    }
    
    //Handle sign up
    if (isset($_POST['signupun']) && isset($_POST['signuppw'])) {
        $signUpUsername = sanitize($conn, $_POST['signupun']);
        $signUpPassword = sanitize($conn, $_POST['signuppw']);

        $status = register($conn, $signUpUsername, $signUpPassword);
    }

    //Check if logged in
    if (isset($_POST['loginun']) && isset($_POST['loginpw'])) {
        $username = sanitize($conn, $_POST['loginun']);
        $password = sanitize($conn, $_POST['loginpw']);
        if (verifyCredentials($conn, $username, $password)) {
            header("Location: homePage.php");
        } else {
            $status = "Incorrect Username or Password";
        }
    }

    //Check if logged in by session
    if (isset($_SESSION['id'])) {
        header("Location: homePage.php");
    }
    
    //Helper Functions
    function usernameInDB($conn, $username) {
        $query = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($query);
        if (!$result) sqlError($conn);

        if ($result->num_rows <= 0) {
            $result->close();
            return False;
        } else {
            $result->close();
            return True;
        }
    }
    
    function register($conn, $username, $password) {
        if (usernameInDB($conn, $username)) return "Username $username already in use.";
        if (empty($username) || empty($password)) return "Signup fields cannot be empty";

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, hash) VALUES ('$username', '$hash')";
        $result = $conn->query($query); //Result is Boolean, Cannot deallocate with close()

        if ($result) {
            return "Successfully registered. Please log in";
        } else {
            sqlError($conn);
        }   
    }

    function verifyCredentials($conn, $username, $password) {
        $query = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($query);
        if (!$result) sqlError($conn);

        if ($result->num_rows <= 0) {
            $result->close();
            return False;
        } else {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $dbhash = $row["hash"];
            $passbool = password_verify($password, $dbhash);
            if ($passbool) {
                $_SESSION["id"] = $row["id"];
                $result->close();
                return True;
            } else {
                $result->close();
                return False;
            }
        }
    }

    //Frontend
    echo <<<_END
        <html><head><title>Login and Signup</title></head><body>
        
        <h3>Login</h3>
        <form method='post' action='signup.php' enctype='multipart/form-data'>
        Input your Username: <input type='text' name='loginun' size='30'>
        <br>
        Input your Password: <input type='text' name='loginpw' size='30'>
        <br>
        <input type='submit' value='Login'>
        </form>

        <h3>Sign Up</h3>
        <form method='post' action='signup.php' enctype='multipart/form-data'>
        Username: <input type='text' name='signupun' size='30'>
        <br>
        Password: <input type='text' name='signuppw' size='30'>
        <br>
        <input type='submit' value='Sign Up'>
        </form>
        <br>
        $status
    _END;

    $conn->close();
?>