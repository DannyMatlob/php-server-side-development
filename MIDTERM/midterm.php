<?php
const CACHE_SIZE = 5;
    function main() {
        //Variables and Magic Numbers
        $file_directory = null;
        
        //File Uploading
        echo <<<_END
        <html><head><title>PHP Form Upload</title></head><body>
        <form method='post' action='midterm.php' enctype='multipart/form-data'>
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
            $file_directory = htmlentities($_FILES['filename']['tmp_name']);
            $name = htmlentities($_FILES['filename']['name']);
            echo "Uploaded txt '$name'<br>";

            //Caching
            $cache = new LRU(CACHE_SIZE);
            $history = $cache->readFile($file_directory);
            foreach ($history as $tuple) {
                echo "Input: " . $tuple[0] . "<br>";
                echo "Output: " . $tuple[1] . "<br>";
                echo "<br>";
            }
        } else {
            echo "No text file has been uploaded yet";
        }
        echo "<br>---TESTING--- <br><br>";
        tester();
    }

    class Node {
        private $next;
        private $prev;
        private $key;
        private $value;
        public function __construct($key, $value) {
            $this->value = $value;
            $this->key = $key;
            $this->prev = null;
            $this->next = null;
        }
        public function getNext() {return $this->next;}
        public function getPrev() {return $this->prev;}
        public function getValue() {return $this->value;}
        public function getKey() {return $this->key;}
        public function setNext($n) {$this->next = $n;}
        public function setPrev($p) {$this->prev = $p;}
        public function setValue($v) {$this->value = $v;}
    }
    class DoublyLinkedList {
        private $size;
        private $head;
        private $tail;

        public function __construct() {
            $this->head = null;
            $this->tail = null;
            $this->size = 0;
        }

        public function push($key, $value) {
            $node = new Node($key, $value);
            if ($this->head == null) { //Handle edge case with no head
                $this->head = $node;
                $this->tail = $node;
            } else {
                $this->head->setPrev($node);
                $node->setNext($this->head);
                $this->head = $node;
            }
            $this->size++;
            return $node;
        }

        public function pop() {
            $tail = $this->tail;
            if ($tail == null) return null; //Edge case no nodes in list
            $prev = $tail->getPrev();
            if ($this->size == 1) { //Edge case of deleting only node in list
                $this->head = null;
                $this->tail = null;
                $this->size--;
                return $tail;
            }
            // Normal Pop Behavior
            $prev->setNext(null);
            $this->tail = $prev;
            $this->size--;
            return $tail;
        }

        public function moveToFront($node) {
            //Remove node from current place in chain
            if ($this->head == $node) return;
            if ($this->tail == $node) {
                $this->tail = $node->getPrev();
            }
            $prev = $node->getPrev();
            $next = $node->getNext();
            if ($prev) $prev->setNext($next);
            if ($next) $next->setPrev($prev);

            //Move to head of chain
            $this->head->setPrev($node);
            $node->setNext($this->head);
            $this->head = $node;
        }

        public function getHead() {return $this->head;}
        public function size() {return $this->size;}
    }
    class LRU {
        private $order;
        private $cache;
        private $maxSize;
        private $lastStatus;
        public function __construct($cache_size) {
            if ($cache_size < 1) {
                throw new Exception("Invalid Cache Size");
            }
            $this->order = new DoublyLinkedList();
            $this->cache = array();
            $this->maxSize = $cache_size;
        }

        private function is_full() {
            if ($this->order->size() > $this->maxSize) {
                return true;
            }
            return false;
        }
        public function get($key) {
            if (array_key_exists($key, $this->cache)) {
                $node = $this->cache[$key];
                $this->order->moveToFront($node);
                return $node->getValue();
            } else {
                return -1;
            }
        }

        private function reset() {
            $this->order = new DoublyLinkedList();
            $this->cache = array();
        }
        public function put($key, $value, $reset) {
            if (is_numeric($value)) {
                if (ctype_digit($value)) {
                    $value = (int) $value;
                } else {
                    $value = (float) $value;
                }
            }
            if ($reset == 1) {
                $this->reset();
                $this->lastStatus = "Resetting Cache";
            }
            if ($value < 0) {
                $this->lastStatus = "No Accept Negative";
                return;
            }
            if (!is_int($value)) {
                $this->lastStatus = "No accept " . gettype( $value );
                return;
            }
            if (array_key_exists($key, $this->cache)) { //Edge case: if key exists already in cache, update
                $node = $this->cache[$key];
                $node->setValue($value);
                $this->order->moveToFront($node);
                $this->lastStatus = "Key already in cache, updating";
            } else {
                $node = $this->order->push($key, $value);
                $this->cache[$key] = $node;
                $this->lastStatus = "Room in cache, Adding";
            }
            if ($this->is_full()) { //Edge case: if cache full, pop last
                $popped = $this->order->pop();
                $popKey = null;
                if ($popped) {
                    $popKey = $popped->getKey();
                }
                unset($this->cache[$popKey]);
                $this->lastStatus = "Cache full, evicting last";
            }
        }

        public function readFile($path) {
            $history = array();
            $fh = fopen($path, 'r') or die("File does not exist or you lack permission to open it");
            while ($line = htmlentities(fgets($fh))) {
                $split = explode(" ", $line);
                $len = count($split);
                $reset = False;
                if ($len == 1) {
                    $this->get($split[0]);
                } else if ($len == 3) {
                    if (trim($split[2]) == "True") $reset = True;
                    $this->put($split[0], $split[1], $reset);
                } else {
                    $this->lastStatus = "Invalid Input";
                }
                $history[] = [$line, $this->statusString()];
            }
            fclose($fh);
            return $history;
        }

        public function statusString() {
            $cache = $this->cache;
            $message = $this->lastStatus;
            $result = "";
            if ($message) $result.= $message . " | ";
            if (count($cache)==0) {
                $result.= "Empty Cache";
                return $result;
            }
            $cur = $this->order->getHead();
            $i = 0;
            while ($cur) {
                $key = $cur->getKey();
                $value = $cur->getValue();
                $cur = $cur->getNext();
                $result.= $key .":". $value;
                $i++;
                if ($i != count($cache)) {
                    $result.= " > ";
                }
            }
            return $result;
        }
    }
    function tester() {
        //List of expected outputs given the test file: TestFile/text.txt
        $expected =    ["Room in cache, Adding | KEY1:1",
                        "Room in cache, Adding | KEY2:2 > KEY1:1",
                        "Room in cache, Adding | KEY3:3 > KEY2:2 > KEY1:1",
                        "Key already in cache, updating | KEY2:2 > KEY3:3 > KEY1:1",
                        "No Accept Negative | KEY2:2 > KEY3:3 > KEY1:1",
                        "Room in cache, Adding | KEY4:4 > KEY2:2 > KEY3:3 > KEY1:1",
                        "No accept double | KEY4:4 > KEY2:2 > KEY3:3 > KEY1:1",
                        "Room in cache, Adding | KEY5:5 > KEY4:4 > KEY2:2 > KEY3:3 > KEY1:1",
                        "No accept string | KEY5:5 > KEY4:4 > KEY2:2 > KEY3:3 > KEY1:1",
                        "Cache full, evicting last | KEY6:6 > KEY5:5 > KEY4:4 > KEY2:2 > KEY3:3",
                        "Room in cache, Adding | KEY7:7",
                        "Key already in cache, updating | KEY7:7"];
        $cache = new LRU(5);

        //INPUT is directory: TestFiles/test.txt
        //OUTPUT is a history of all string outputs for that text file 
        $history = $cache->readFile("TestFiles/test.txt");

        //Iterate through history and perform a check against expected values
        $i = 0;
        foreach ($history as $tuple) {
            $expectedString = $expected[$i];
            echo "INPUT: " . $tuple[0] . "<br>";
            $string = $tuple[1];
            echo "OUTPUT: " . $string . "<br>";
            echo "EXPECTED: " . $expectedString . "<br>";
            if ($string == $expectedString) {
                echo "TEST PASSED <br>";
            } else {
                echo "TEST FAILED <br>";
            }
            $i++;
            echo "<br>";
        }
    }
    main();
?>