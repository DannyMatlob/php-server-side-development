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
    if (isset($_POST['name']) && isset($_POST['id']) && isset($_POST['email']) && isset($_POST['password'])) {
        $signUpName = sanitize($conn, $_POST['name']);
        $signUpID = sanitize($conn, $_POST['id']);
        $signUpEmail = sanitize($conn, $_POST['email']);
        $signUpPassword = sanitize($conn, $_POST['password']);

        $status = register($conn, $signUpName, $signUpID, $signUpEmail, $signUpPassword);
    }

    //Check if logged in
    if (isset($_POST['loginemail']) && isset($_POST['loginpassword'])) {
        $email = sanitize($conn, $_POST['loginemail']);
        $password = sanitize($conn, $_POST['loginpassword']);
        if (verifyCredentials($conn, $email, $password)) {
            header("Location: homePage.php");
        } else {
            $status = "Incorrect Email or Password";
        }
    }

    //Check if logged in by session
    if (isset($_SESSION['id'])) {
        header("Location: firstPage.php");
    }
    
    //Helper Functions
    function emailInDB($conn, $email) {
        $query = "SELECT * FROM students WHERE email='$email'";
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
    function validateEmail($email) {
        $dot = strpos($email, ".");
        $at = strpos($email, "@");
        
        if ($email == "") {
            return "Email cannot be empty.\n";
        } elseif (!(($at > 0) && ($dot > $at)) || preg_match('/[^a-zA-Z0-9.@_-]/', $email)) {
            return "The Email address is invalid.\n";
        }
        
        return "";
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
    
    
    function register($conn, $username, $sid, $email, $password) {
        if (emailInDB($conn, $email)) return "Email $email already in use.";
        if (empty($username) || empty($sid) || empty($email) || empty($password)) return "Signup fields cannot be empty";
        if (!preg_match('/^\d{9}$/', $sid)) return "ID Must be exactly 9 digits";
        $emailStatus = validateEmail($email);
        if ($emailStatus!="") return $emailStatus;
        $passStatus = validatePassword($password);
        if ($passStatus!="") return $passStatus;

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO students (username, student_id, email, hash) VALUES ('$username', '$sid', '$email', '$hash')";
        $result = $conn->query($query);

        if ($result) {
            return "Successfully registered. Please log in";
        } else {
            sqlError($conn);
        }   
    }

    function verifyCredentials($conn, $email, $password) {
        $query = "SELECT * FROM students WHERE email='$email'";
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
            <title>Second Page</title>
            <style>
                .signup {
                border:1px solid #999999; font: normal 14px helvetica; color: #444444;
                }
            </style>
            <script src="validation.js"></script>
            <script>
                function validateSignUp(form) {
                    let fail = ""
                    fail += validateName(form.name.value)
                    fail += validateID(form.id.value)
                    fail += validateEmail(form.email.value)
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
        <form method="post" action="secondPage.php" onsubmit="return validate(this)">
            <tr><td>Email</td>
                <td><input type="text" maxlength="100" name="loginemail"></td></tr>
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
            <form method="post" action="secondPage.php" onsubmit="return validateSignUp(this)">
                <tr><td>Name</td>
                    <td><input type="text" maxlength="32" name="name"></td></tr>
                <tr><td>Student ID</td>
                    <td><input type="text" maxlength="9" name="id"></td></tr>
                <tr><td>Email</td>
                    <td><input type="text" maxlength="100" name="email"></td></tr>
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