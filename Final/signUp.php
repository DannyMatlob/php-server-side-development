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
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $signUpName = sanitize($conn, $_POST['username']);
        $signUpPassword = sanitize($conn, $_POST['password']);

        $status = register($conn, $signUpName, $signUpPassword);
    }

    //Check if logged in
    if (isset($_POST['loginuser']) && isset($_POST['loginpassword'])) {
        $username = sanitize($conn, $_POST['loginuser']);
        $password = sanitize($conn, $_POST['loginpassword']);
        if (verifyCredentials($conn, $username, $password)) {
            header("Location: question.php");
        } else {
            $status = "Incorrect Email or Password";
        }
    }

    //Check if logged in by session
    if (isset($_SESSION['id'])) {
        header("Location: question.php");
    }
    
    //Helper Functions
    function usernameInDb($conn, $username) {
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

    function validatePassword($password) {
        if ($password == "") {
            return "Password cannot be empty.\n";
        }
        if (strlen($password) < 6) {
            return "Password must be at least 6 characters.\n";
        }
        if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)) {
            return "Password must include at least 1 uppercase and at least 1 lowercase.\n";
        }
        return "";
    }
    
    
    function register($conn, $username, $password) {
        if (usernameInDB($conn, $username)) return "Username $username already in use.";
        if (empty($username) || empty($password)) return "Signup fields cannot be empty";
        $passStatus = validatePassword($password);
        if ($passStatus!="") return $passStatus;

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, hash) VALUES ('$username', '$hash')";
        $result = $conn->query($query);

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
    <html>
        <head>
            <title>Sign Up Page</title>
            <style>
                .signup {
                border:1px solid #999999; font: normal 14px helvetica; color: #444444;
                }
            </style>
            <script src="validation.js"></script>
            <script>
                function validateSignUp(form) {
                    let fail = ""
                    fail += validateUsername(form.username.value)
                    fail += validatePassword(form.password.value)
                    if (fail == "") {
                        return true
                    } else { 
                        alert(fail)
                        return false 
                    }
                    return false 
                }
            </script>
        </head>
    
    <body>
        <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
        <th colspan="2" align="center">Login Form</th>
        <form method="post" action="signUp.php" onsubmit="return validate(this)">
            <tr><td>Username</td>
                <td><input type="text" maxlength="64" name="loginuser"></td></tr>
            <tr><td>Password</td>
                <td><input type="text" maxlength="64" name="loginpassword"></td></tr>
            <tr><td colspan="2" align="center">
                <input type="submit" value="Login"></td>
            </tr>
        </form>
        </table>
        <br>
        <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
            <th colspan="2" align="center">Signup Form</th>
            <form method="post" action="signUp.php" onsubmit="return validateSignUp(this)">
                <tr><td>Username</td>
                    <td><input type="text" maxlength="64" name="username"></td></tr>
                <tr><td>Password</td>
                    <td><input type="text" maxlength="64" name="password"></td></tr>
                <tr><td colspan="2" align="center">
                    <input type="submit" value="Signup"></td>
                </tr>
            </form>
        </table>
        $status
    </body>
    </html>
    
    _END;

    $conn->close();
?>