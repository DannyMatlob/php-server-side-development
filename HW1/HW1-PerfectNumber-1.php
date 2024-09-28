<?php
    //Primary function: handles solving problem and printing at the same time
    function isPerfectNumber($input) {
        echo "<br><br>Input = $input<br>";
        if (!is_int($input) || $input<0) {
            echo "input must be a positive integer.";
            return false;
        }
        $sumOfDivisors = 0;
        $divisors = array();

        for ($i = $input - 1; $i > 0; $i--) {
            if ($input % $i == 0) {
                $divisors[] = $i;
            }
        }
        for ($i = 0; $i < count($divisors); $i++) {
            $sumOfDivisors += $divisors[$i];
        }

        if ($sumOfDivisors == $input) {
            echo "Yes this is a perfect number. Proof: ";
            printDivisorsSummary($divisors, $sumOfDivisors, $input);
            echo " which is equal to the input";
            return true;
        } else {
            echo "No this is not a perfect number. Proof: ";
            printDivisorsSummary($divisors, $sumOfDivisors, $input);
            echo " which is not equal to the input";
            return false;
        }
    }

    //I had to make a function just to print things more cleanly. This code would've been repeated twice above otherwise.
    function printDivisorsSummary($divisors, $sumOfDivisors, $input) {
        echo "The divisors of $input excluding self are [";
        $count = count($divisors);
        foreach ($divisors as $index => $i) {
            echo $i;
            if ($index < $count - 1) {
                echo ", ";
            }
        }
        echo "] and ";

        foreach ($divisors as $index => $i) {
            echo $i;
            if ($index < $count - 1) {
                echo " + ";
            }
        }
        echo " = $sumOfDivisors ";
    }

    //I realize there's a lot of repeated code but I'm trying not to make too many functions for this simple assignment
    function tester_function() {
        $resultString = isPerfectNumber("String");
        $resultString = $resultString ? "true" : "false";
        echo "<br>UNIT TEST: <br> Output: $resultString<br>" . 'Is output from isPerfectNumber("String") = false?<br>';
        if ($resultString == false) {
            echo "Yes, test passed";
        } else echo "No, test failed";

        $resultNegative = isPerfectNumber(-5);
        $resultNegative = $resultNegative ? "true" : "false";
        echo "<br>UNIT TEST: <br> Output: $resultNegative<br>" . 'Is output from isPerfectNumber(-5) = false?<br>';
        if ($resultNegative == false) {
            echo "Yes, test passed";
        } else echo "No, test failed";

        $result6 = isPerfectNumber(6);
        $result6 = $result6 ? "true" : "false";
        echo "<br>UNIT TEST: <br> Output: $result6<br>" . 'Is output from isPerfectNumber(6) = true?<br>';
        if ($result6 == true) {
            echo "Yes, test passed";
        } else echo "No, test failed";

        $result12 = isPerfectNumber(12);
        $result12 = $result12 ? "true" : "false";
        echo "<br>UNIT TEST: <br> Output: $result12<br>" . 'Is output from isPerfectNumber(12) = false?<br>';
        if ($result12 == false) {
            echo "Yes, test passed";
        } else echo "No, test failed";

        $result20 = isPerfectNumber(20);
        $result20 = $result20 ? "true" : "false";
        echo "<br>UNIT TEST: <br> Output: $result20<br>" . 'Is output from isPerfectNumber(20) = false?<br>';
        if ($result20 == false) {
            echo "Yes, test passed";
        } else echo "No, test failed";
    }
    
    tester_function();
?>
