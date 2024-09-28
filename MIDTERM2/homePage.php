<?php
    session_start();
    session_regenerate_id();
    require_once("login.php");
    require_once("utils.php");

    //Vars and Consts
    const ONE_DAY_IN_SECONDS = 60 * 60 * 24;
    $status = "";

    try {
        $conn = new mysqli($hn, $un, $pw, $db);
    } catch (Exception $e) {
        sqlError($conn);
    }

    //Check if Logged In
    if (!isset($_SESSION['id'])) {
        header("Location: signup.php");
    } else {
        $id = sanitize($conn, $_SESSION['id']);
    }

    //Handle Expand Toggling
    if (isset($_POST['expand'])) {
        toggleExpand($conn);
    }

    //Handle Expand Action
    if (isset($_COOKIE['expand'])) {
        $cookieValue = sanitize($conn, $_COOKIE['expand']);
        $expand = ($cookieValue === 'True') ? True : False;
    } else {
        toggleExpand($conn);
        $expand = False;
    }

    //Handle making a post
    if ($_FILES) {
        if (!empty($_POST['title'])) {
            switch($_FILES['filename']['type']) {
                case 'text/plain'	: $ext = 'txt'; break;
                default			    : $ext = ''; break;
            }
            if (!$ext) {
                $status = "Filetype not accepted/Cannot be empty. Upload a text file.";  
            } else {
                $file_directory = $_FILES['filename']['tmp_name'];
                $name = sanitize($conn, $_FILES['filename']['name']);

                //Make the sql call
                $id = sanitize($conn, $_SESSION['id']);
                $title = sanitize($conn, $_POST['title']);
                $content = sanitize($conn, file_get_contents($file_directory)); //Load file from tmp directory
                makePost($conn, $id, $title, $content);
                $status = "Thread: $title | File '$name' Uploaded<br>";
            }
        } else {
            $status = "Thread Name can't be empty";
        }
    }

    //Handle logout
    if (isset($_POST['logout'])) {
        //Destroy Expand Cookie
        unset($_COOKIE['expand']);
        setcookie('expand', '', time() - ONE_DAY_IN_SECONDS, '/');

        //Destroy Session
        destroySessionAndData();
        header("Refresh: 0");
    }

    //Helper Functions
    function makePost($conn, $id, $title, $content) {
        $query = "INSERT INTO threads (id, title, content) VALUES ($id, '$title', '$content')";
        $result = $conn->query($query); //Result is Boolean, Cannot deallocate with close()
        if (!$result) sqlError($conn);

        return True;
    }
    function fetchPosts($conn, $id, $expand) {
        $query = "SELECT * FROM threads WHERE id=$id";
        $result = $conn->query($query);
        if (!$result) sqlError($conn);

        for ($i = 0; $i<$result->num_rows; $i++) {
            $result->data_seek($i);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $title = $row["title"];
            $content = $row["content"];
            if (!$expand) $content = substr($content, 0, 20) . "..."; //Truncate content if not expanded
            echo <<<_END
                <h4>$i: $title</h4>
                $content <br>
                -------------------------------------<br>
            _END;
        }
        $result->close();
    }

    function toggleExpand ($conn) {
        if(isset($_COOKIE['expand'])) {
            $cookieValue = sanitize($conn, $_COOKIE['expand']);
            $newValue = ($cookieValue === 'True') ? 'False' : 'True';
        } else {
            $newValue = 'False';
        }
        setcookie('expand', $newValue, time() + ONE_DAY_IN_SECONDS, '/');
        $_COOKIE['expand'] = $newValue;
    }

    //Front End
    echo <<<_END
        <form method='post' action='homePage.php' enctype='multipart/form-data'>
        <input type='submit' value='Logout' name='logout'>
        </form>
        <h2> Make a new post </h2>
        <form method='post' action='homePage.php' enctype='multipart/form-data'>
        Thread Name: <br>
        <input type='text' name='title' size='30'>
        <br><br>
        Content (text files only): <input type='file' name='filename' size='10'>
        <br>
        <input type='submit' value='Upload'>
        </form><br>
        $status

        <h2> Your Threads </h2>
        <form method='post' action='homePage.php' enctype='multipart/form-data'>
        <input type='submit' name='expand' value='Expand/Collapse All'>
        </form><br>
    _END;
    fetchPosts($conn, $id, $expand);

    //Cleanup
    $conn->close();
?>
