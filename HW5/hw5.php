<?php
    require_once("login.php");
    require_once("pages.php");
    require_once("utils.php");

    //Set up variables
    $loggedIn = False;
    $status = "";
    $displayname = "";
    $id = -1;

    try {
        $conn = new mysqli($hn, $un, $pw, $db);
    } catch (Exception $e) {
        sqlError();
    }
    
    //Handle logout
    if (isset($_POST['logout'])) {
        unset($_COOKIE['id']);
        setcookie('id', '', time() - 3600, '/');
        unset($_COOKIE['displayname']);
        setcookie('displayname', '', time() - 3600, '/');
    }

    //Handle sign up
    if (isset($_POST['signupName']) && isset($_POST['signupun']) && isset($_POST['signuppw'])) {
        $signUpDisplayname = sanitize($conn, $_POST['signupName']);
        $signUpUsername = sanitize($conn, $_POST['signupun']);
        $signUpPassword = sanitize($conn, $_POST['signuppw']);

        $status = register($conn, $signUpDisplayname, $signUpUsername, $signUpPassword);
    }

    //Check if logged in
    if (isset($_POST['loginun']) && isset($_POST['loginpw'])) {
        $username = sanitize($conn, $_POST['loginun']);
        $password = sanitize($conn, $_POST['loginpw']);
        if (verifyCredentials($conn, $username, $password)) {
            $loggedIn = True;
            header("Refresh:0");
        } else {
            $status = "Incorrect Username or Password";
        }
    }

    //Check if logged in by cookies
    if (isset($_COOKIE['displayname']) && isset($_COOKIE['id'])) {
        $displayname = sanitize($conn, $_COOKIE['displayname']);
        $id = sanitize($conn, $_COOKIE['id']);
        if (checkDisplayNameWithID($conn, $displayname, $id)) {
            $loggedIn = True;
            $status = "";
        }
    }

    //Display page
    echo "Hello $displayname!<br>";
    if ($loggedIn) {
        if (isset($_POST["title"]) && isset($_POST["content"])) {
            $title = sanitize($conn, $_POST["title"]);
            $content = sanitize($conn, $_POST["content"]);
            header("Refresh: 0"); //Clear post array
            if ($id<0) sqlError();
            if (empty($title) || empty($content)) {
                echo "Title or Content cannot be empty.";
            } else {
                makePost($conn, $id, $title, $content);
                echo "Post added successfully!";
            }
        }
        homePage();
        fetchPosts($conn, $id);
    } else {
        loginSignUpPage();
        echo $status;
    }
    
    //Helper Functions
    function checkDisplayNameWithID($conn, $displayname, $id) {
        $query = "SELECT * FROM users WHERE id=$id";
        $result = $conn->query($query);
        if (!$result) sqlError();

        $row = $result->fetch_array(MYSQLI_ASSOC);
        if ($row["displayname"] == $displayname) {
            $result->close();
            return True;
        } else {
            $result->close();
            return False;
        }
    }

    function usernameInDB($conn, $username) {
        $query = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($query);
        if (!$result) sqlError();

        if ($result->num_rows <= 0) {
            $result->close();
            return False;
        } else {
            $result->close();
            return True;
        }
    }
    
    function register($conn, $displayname, $username, $password) {
        if (usernameInDB($conn, $username)) return "Username $username already in use.";
        if (empty($displayname) || empty($username) || empty($password)) return "Signup fields cannot be empty";
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (displayname, username, hash) VALUES ('$displayname', '$username', '$hash')";
        $result = $conn->query($query);
        if ($result) {
            return "Successfully registered. Please log in";
        } else {
            sqlError();
        }   
    }

    function verifyCredentials($conn, $username, $password) {
        $query = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($query);
        if (!$result) sqlError();

        if ($result->num_rows <= 0) {
            $result->close();
            return False;
        } else {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $dbhash = $row["hash"];
            $passbool = password_verify($password, $dbhash);
            if ($passbool) {
                setcookie('displayname', $row["displayname"], time() + 60 * 60 * 24 * 7, '/');
                setcookie('id', $row["id"], time() + 60 * 60 * 24 * 7, '/');
                $result->close();
                return True;
            } else {
                $result->close();
                return False;
            }
        }
    }
    function makePost($conn, $id, $title, $content) {
        $query = "INSERT INTO posts (id, title, content) VALUES ($id, '$title', '$content')";
        $result = $conn->query($query);
        if (!$result) sqlError();

        return True;
    }
    function fetchPosts($conn, $id) {
        $query = "SELECT * FROM posts WHERE id=$id";
        $result = $conn->query($query);
        if (!$result) sqlError();

        for ($i = 0; $i<$result->num_rows; $i++) {
            $result->data_seek($i);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $title = $row["title"];
            $content = $row["content"];
            echo <<<_END
                <h4>$i: $title</h4>
                $content <br>
                -------------------------------------<br>
            _END;
        }
        $result->close();
    }

    $conn->close();
?>