<?php
    const GRID_SIZE = 20;
    const SEQUENCE_LENGTH = 5;
    const MAX_NUMS = GRID_SIZE * GRID_SIZE;
    function main() {
        //Variables and Magic Numbers
        $file_directory = null;
        
        //File Uploading
        echo <<<_END
        <html><head><title>PHP Form Upload</title></head><body>
        <form method='post' action='hw2.php' enctype='multipart/form-data'>
        Select Text File: <input type='file' name='filename' size='10'>
        <input type='submit' value='Upload'>
        </form>
        _END;

        if ($_FILES) {
            switch($_FILES['filename']['type']) {
                case 'text/plain'	: $ext = 'txt'; break;
                default			    : $ext = ''; break;
            }
            if (!$ext) {
                die("Filetype not accepted. Upload a text file.");  
            }
            $file_directory = $_FILES['filename']['tmp_name'];
            $name = $_FILES['filename']['name'];
            echo "Uploaded txt '$name'<br>";

            //Solving
            $text = Solver::readUpload($file_directory);
            $solution = Solver::largestProduct($text);
            //Print result
            Solver::printSolution($solution);
        } else {
            echo "No text file has been uploaded yet";
        }
        echo "<br><br><br>";
        testIdeal(); echo "<br>";
        testBad(); echo "<br>";
        testUgly(); echo "<br>";
        echo "</body></html>";
    } 

    class Solver {
        //Read in text file and return a string
        public static function readUpload($path) {
            $contents = htmlentities(file_get_contents($path)); //Load file from tmp directory
            $contents = preg_replace("/\s+/", "", $contents); //Remove whitespace
            $contents = preg_replace("/[^0-9]/", "0", $contents); //Fill non-nums with 0
            $length = strlen($contents);
            if ($length < MAX_NUMS) { //Handle ugly cases
                return null;
            } else if ($length > MAX_NUMS) {
                echo "Text file formatted incorrectly (above ". MAX_NUMS ." numbers). Truncating down to 400.<br>";
                $contents = substr($contents, 0, MAX_NUMS);
                $length = strlen($contents);
            }
            return $contents;
        }
        
        //Creates 2D array from string, text must be exactly 400 characters
        public static function parseGrid($text) {
            $grid = array();
            for ($i = 0; $i<GRID_SIZE; $i++) {
                $row = array();
                for ($j = 0; $j<GRID_SIZE; $j++) {
                    //echo $text[$i *20 + $j];
                    $row[] = $text[$i *GRID_SIZE + $j];
                }
                $grid[] = $row;
                //echo '<br>';
            }
            //echo "<br>";
            return $grid;
        }

        //Splits number into digits and adds up individual factorials
        //Returns a results array: (factorialOfDigits, explanation)
        public static function factorialOfDigits($num) {
            $sum = 0;
            $digits = str_split($num);
            $explanation = "Sum of Factorial = ";

            foreach ($digits as $digit) {
                $explanation .= $digit . "! + ";
                $sum += Solver::factorial($digit);
            }
            $explanation = substr($explanation, 0, -2) . " = $sum";
            return array($sum, $explanation);
        }

        //Simple Factorial Function
        public static function factorial($n) {
            if ($n == 0) {
              return 1;
            }
            return $n * Solver::factorial($n - 1);
        }

        //Simple function to find product of all elements in array
        public static function product($arr) {
            $prod = 1;
            foreach ($arr as $n) {
                $prod *= $n;
            }
            return $prod;
        }

        //Calculate largestProduct and factorial of product digits from 400 character string
        //Returns a results array: (largestProduct, FactorialOfProduct, arrayOfDigitsFound)
        public static function largestProduct($text) {
            if ($text == null) return null;
            $grid = Solver::parseGrid($text);
            $largestDigits = null;
            $largest = 0;
            for ($i = 0; $i<GRID_SIZE; $i++) {
                for ($j = 0; $j<GRID_SIZE; $j++) {
                    if ($j<GRID_SIZE - SEQUENCE_LENGTH + 1) { //Check rightmost 5, right diagonals, downwards
                        //Right check
                        $nums = array($grid[$i][$j], $grid[$i][$j+1], $grid[$i][$j+2], $grid[$i][$j+3], $grid[$i][$j+4]);
                        $product = Solver::product($nums);
                        if ($product > $largest) {
                            $largest = $product; $product = 0; $largestDigits = $nums;
                        }

                        //Down check
                        if ($i<GRID_SIZE - SEQUENCE_LENGTH + 1) {
                            //Diagonal down right check
                            $nums = array($grid[$i][$j], $grid[$i+1][$j+1], $grid[$i+2][$j+2], $grid[$i+3][$j+3], $grid[$i+4][$j+4]);
                            $product = Solver::product($nums);
                            if ($product > $largest) {
                                $largest = $product; $product = 0; $largestDigits = $nums;
                            }

                            //Downward check
                            $nums = array($grid[$i][$j], $grid[$i+1][$j], $grid[$i+2][$j], $grid[$i+3][$j], $grid[$i+4][$j]);
                            $product = Solver::product($nums);
                            if ($product > $largest) {
                                $largest = $product; $product = 0; $largestDigits = $nums;
                            }
                        }
                    }
                    if ($j>=SEQUENCE_LENGTH - 1) { //Check leftmost 5 and left diagonals
                        $nums = array($grid[$i][$j], $grid[$i][$j-1], $grid[$i][$j-2], $grid[$i][$j-3], $grid[$i][$j-4]);
                        $product = Solver::product($nums);
                        if ($product > $largest) {
                            $largest = $product; $product = 0; $largestDigits = $nums;
                        }

                        //Diagonal down left check
                        if ($i<GRID_SIZE - SEQUENCE_LENGTH + 1) {
                            $nums = array($grid[$i][$j], $grid[$i+1][$j-1], $grid[$i+2][$j-2], $grid[$i+3][$j-3], $grid[$i+4][$j-4]);
                            $product = Solver::product($nums);
                            if ($product > $largest) {
                                $largest = $product; $product = 0; $largestDigits = $nums;
                            }
                        }
                    }
                }
            }
            $FOD = Solver::factorialOfDigits($largest);
            return array($largest, $FOD, $largestDigits);
        }

        public static function printSolution($solution) {
            if ($solution) {
                echo "<br>Largest sum: " . $solution[2][0] . 
                ' * '. $solution[2][1] . 
                ' * '. $solution[2][2] . 
                ' * '. $solution[2][3] . 
                ' * '. $solution[2][4] . ' = ' . $solution[0] . ' — ' . $solution[1][1];
            } else {
                echo "Text file formatted incorrectly (below ". MAX_NUMS ." numbers). Try again.";
            }
        }
    }
    //Run main so the whole thing works lol
    main();

    function testIdeal () {
        echo "Test 1: Ideal Input.<br>";
        $input = 'TestFiles/ideal.txt';
        $expected = 40824;
        $output = Solver::largestProduct(Solver::readUpload($input));
        if ($output) {
            $output = $output[0];
            $outputString = $output;
        } else {
            $outputString = "null";
        }
        echo "Is (output) $outputString = (expected) $expected? ";
        if ($output == $expected) {
            echo "Yes, unit test passed.<br>";
        } else {
            echo "No, unit test failed.<br>";
        }
    } 
    function testBad () {
        echo "Test 2: Bad Input.<br>";
        $input = 'TestFiles/less400.txt';
        $expected = null;
        $expectedString = "null";
        $output = Solver::largestProduct(Solver::readUpload($input));
        if ($output) {
            $output = $output[0];
            $outputString = $output;
        } else {
            $outputString = "null";
        }
        echo "Is (output) $outputString = (expected) $expectedString? ";
        if ($output == $expected) {
            echo "Yes, unit test passed.<br>";
        } else {
            echo "No, unit test failed.<br>";
        }
    } 
    function testUgly () {
        echo "Test 3: Ugly Input.<br>";
        $input = 'TestFiles/alphabetChars.txt';
        $expected = 40824;
        $output = Solver::largestProduct(Solver::readUpload($input));
        if ($output) {
            $output = $output[0];
            $outputString = $output;
        } else {
            $outputString = "null";
        }
        echo "Is (output) $outputString = (expected) $expected? ";
        if ($output == $expected) {
            echo "Yes, unit test passed.<br>";
        } else {
            echo "No, unit test failed.<br>";
        }
    } 
?>


