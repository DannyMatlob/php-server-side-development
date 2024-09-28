<?php
    session_start();
    session_regenerate_id();
    require_once("login.php");
    require_once("utils.php");

    //Vars and Consts
    const ONE_DAY_IN_SECONDS = 60 * 60 * 24;
    const MYSQLI_DUPLICATE_ERR = 1062;
    $status = "";
    $question = "No questions available for this user.";

    //Connect to DB
    try {
        $conn = new mysqli($hn, $un, $pw, $db);
    } catch (Exception $e) {
        sqlError($conn);
    }

    //Check if Logged In
    if (!isset($_SESSION['id'])) {
        header("Location: signUp.php");
    } else {
        $id = sanitize($conn, $_SESSION['id']);
    }

    //Handle logout
    if (isset($_POST['logout'])) {
        //Destroy Session
        destroySessionAndData();
        header("Refresh: 0");
    }

    //Handle File Upload
    if ($_FILES) {
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
            readQuestionUpload($conn, $id, $file_directory);
            $status = "File '$name' Uploaded<br>";
        }
    }

    //Helper Functions
    function readQuestionUpload ($conn, $id, $filePath) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = sanitize($conn, $line);
            if ($line != "") {
               addQuestionDB($conn, $id, $line); 
            }
        }
    }

    function addQuestionDB ($conn, $id, $question) {
        $query = "INSERT INTO questions (user_id, question) VALUES ($id, '$question')";
        $result = $conn->query($query);
        if (!$result) {
            if ($conn->errno == MYSQLI_DUPLICATE_ERR) {
                //echo "Duplicate question, moving on <br>";
            } else {
                sqlError($conn);
            }
        }

        return True;
    }
    
    function fetchRandomQuestion ($conn, $id) {
        $query = "SELECT * FROM questions WHERE user_id=$id";
        $result = $conn->query($query);
        if (!$result) sqlError($conn);

        if ($result->num_rows == 0) {
            $result->close();
            return "No questions available.";
        }
        
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row["question"];
        }
        $randomIndex = rand(0, $result->num_rows - 1);

        $result->close();
        return $questions[$randomIndex];
    }

    //Front End
    echo <<<_END
        <head>
            <title>Questions</title>
        </head>
        <body>
            <form method='post' action='question.php' enctype='multipart/form-data'>
            <input type='submit' value='Logout' name='logout'> </form>
            <h1> The Wheel of Questions </h1>
            <form method='post' action='question.php' enctype='multipart/form-data'>
                <h3> Add a set of questions </h3>
                <form method='post' action='question.php' enctype='multipart/form-data'>
                (text files only): 
                    <input type='file' name='filename' size='10'>
                    <input type='submit' value='Upload'>
            </form><br>
            $status

            <h2> Current Question </h2>
            <form method='post' action='question.php' enctype='multipart/form-data'>
                <input type='submit' value='New Question'>
            </form> <br>
        </body>
    _END;
    echo fetchRandomQuestion($conn, $id);
    //Cleanup
    $conn->close();
?>
