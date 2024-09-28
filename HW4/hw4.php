<?php
    require_once("login.php");
    require_once("utils.php");

    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) sqlError();
    function main($conn) {
        //Setup
        $interface = new BankInterface($conn);
        createForm();
        
        //Verify Form
        if (isset($_POST['email'])) {
            $email = sanitizeSQL($conn, $_POST['email']);
            if ($_FILES) {
                switch($_FILES['filename']['type']) {
                    case 'text/plain'	: $ext = 'txt'; break;
                    default			    : $ext = ''; break;
                }
                if (!$ext) {
                    die("Filetype not accepted. Upload a text file.");  
                }
                $file_directory = htmlentities($_FILES['filename']['tmp_name']);
                $fileName = htmlentities($_FILES['filename']['name']);
                echo "Uploaded txt '$fileName'<br>";

                //Run the file
                $interface->executeFile($email, $file_directory);
            } else {
                echo "No text file has been uploaded yet";
            }
        } else {
            $fileName = "(Not entered)";
        }

        //Print the balances
        $interface->showBalances();
    }

    class BankInterface {
        private $conn;

        public function __construct($connection) {
            $this->conn = $connection;
        }
        public function executeFile($email, $dir) {
            echo "<br>";
            $email = sanitizeSQL($this->conn, $email);
            $selectQuery = "SELECT * FROM balance WHERE email=\"$email\"";
            $emailRow = $this->conn->query($selectQuery)->fetch_array(MYSQLI_ASSOC);
            if (!$emailRow) {
                $insertQuery = "INSERT INTO balance (email) VALUES (\"$email\")";
                $res = $this->conn->query($insertQuery);
                if (!$res) sqlError();
            }
            $lines = file($dir, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = sanitizeSQL($this->conn, $line);
                if (is_numeric($line)) {
                    $line = (float) $line;
                    $newBal = $emailRow["bal"] + $line;
                    $updateQuery = "UPDATE balance SET bal = $newBal WHERE email = \"$email\"";
                    $this->conn->query($updateQuery);
                    $emailRow = $this->conn->query($selectQuery)->fetch_array(MYSQLI_ASSOC);
                    if (!$emailRow) sqlError();
                    if ($line > 0) {
                        echo "Adding \$$line to balance of $email<br>";
                    } else if ($line < 0) {
                        $line = -$line;
                        echo "Subtracting \$$line from balance of $email <br>";
                    } else {
                        echo "No change to balance of $email <br>";
                    }
                } else {
                    echo "Error, improper format, " . gettype($line) . " \"$line\" not accepted<br>";
                }
            }
            echo "<br><br>";
        }

        public function showBalances() {
            $result = $this->conn->query("SELECT * FROM balance");
            if (!$result) sqlError();
            $rows = $result->num_rows;
            for ($j = 0 ; $j < $rows ; ++$j) {
                $result->data_seek($j);
                $row = $result->fetch_array(MYSQLI_ASSOC);
                echo $row["email"] . " has a balance of: " . $row["bal"] ."<br>";
            }
        }
    }
    main($conn);
?>