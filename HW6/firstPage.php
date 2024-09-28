<?php
    session_start();
    session_regenerate_id();
    require_once("login.php");
    require_once("utils.php");

    //Vars and Consts
    const ONE_DAY_IN_SECONDS = 60 * 60 * 24;
    $status = "";

    //Connect to DB
    try {
        $conn = new mysqli($hn, $un, $pw, $db);
    } catch (Exception $e) {
        sqlError($conn);
    }

    //Check if Logged In
    if (!isset($_SESSION['id'])) {
        header("Location: secondPage.php");
    } else {
        $id = sanitize($conn, $_SESSION['id']);
    }

    //Handle logout
    if (isset($_POST['logout'])) {
        //Destroy Session
        destroySessionAndData();
        header("Refresh: 0");
    }

    //Helper Functions
    function checkForm($conn) {
        $name = sanitize($conn, $_POST["name"]);
        $id = sanitize($conn, $_POST["id"]);
        if ($name=="") {
            echo "Name must not be empty";
            return;
        }
        if (!preg_match('/^\d{9}$/', $id)) {
            echo "ID Must be 9 digits";
            return;
        }
        fetchAdvisors($conn, $name, $id);
        
    }
    function fetchAdvisors($conn, $name, $id) {
        $query1 = "SELECT * FROM students WHERE username='$name' AND student_id='$id'";
        $result1 = $conn->query($query1);
        if (!$result1) sqlError($conn);
        if ($result1->num_rows==0) {
            $status = "Non-matching Name and ID";
            echo $status;
            return;
        }

        $query2 = "SELECT * FROM advisors";
        $result2 = $conn->query($query2);
        if (!$result2) sqlError($conn);

        $status = "No Advisors Found";
        $lastTwoDigits = intval(substr($id, -2));
        for ($i = 0; $i<$result2->num_rows; $i++) {
            $result2->data_seek($i);
            $row = $result2->fetch_array(MYSQLI_ASSOC);
            $upper = $row['student_id_upper'];
            $lower = $row['student_id_lower'];

            if ($lastTwoDigits >= $lower && $lastTwoDigits <= $upper) {
                $name = $row['full_name'];
                $email = $row['email'];
                $phone = $row['phone'];
                echo "<b>ADVISOR:</b> $name, <b>EMAIL:</b> $email, <b>TELEPHONE:</b> $phone <br>";
                $status = "";
            }
        }
        echo $status . "<br>";
        $result1->close();
        $result2->close();
    }

    //Front End
    echo <<<_END
        <head>
            <title>Second Page</title>
            <style>
                .signup {
                border:1px solid #999999; font: normal 14px helvetica; color: #444444;
                }
            </style>
            <script src="validation.js"></script>
            <script>
                function validateSearch(form) {
                    fail = ""
                    fail += validateName(form.name.value)
                    fail += validateID(form.id.value)
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
        <form method='post' action='firstPage.php' enctype='multipart/form-data'>
        <input type='submit' value='Logout' name='logout'>
        </form>
        <h2> Search for Advisors </h2>
        <form method='post' action='firstPage.php' enctype='multipart/form-data' onsubmit="return validateSearch(this)">
        Student Name: <br>
        <input type='text' name='name' size='30' maxlength="100">
        <br>Student ID: <br>
        <input type='text' name='id' size='30' maxlength="9">
        <br>
        <input type='submit' value='Search'>
        </form><br>
        </body>
    _END;
    if (isset($_POST["name"]) && isset($_POST["id"])) {
        checkForm($conn);  
    }
    
    //Cleanup
    $conn->close();
?>
