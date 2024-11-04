<?php
require_once('includes/dbConnect.php');

//error_log('Running filterValidation.php');

if (!validateAllVariableIn($_GET)) {
   // error_log('Invalid GET parameters detected, testing to index.php');
    header("location:index.php");
    exit;
}

filterAllVariable($_GET);
filter($_GET);
filter($_POST);

if (!validateAllVariableIn($_POST)) {
   // error_log('Invalid POST parameters detected, redirecting to index.php');
    header("location:index.php");
    exit;
}

filterAllVariable($_POST);

function filterAllVariable(&$data) {
    $searchArr = array('<', '>');
    $replaceArr = array('&lt;', '&gt;');
    foreach ($data as $key => $val) {
        $data[$key] = filter($val); // Use the updated filter function
        $data[$key] = str_replace($searchArr, $replaceArr, $data[$key]);
    }
}

function validateAllVariableIn($data) {
    if (count($data) == 0) return true;

    foreach ($data as $value) {
        if (trim($value)) {
            if (!validateQueryStringVariable($value)) {
                error_log('Invalid variable: ' . $value);
                return false;
            }
        }
    }
    return true;
}

function validateQueryStringVariable($str) {
    if (!validateOSInjection($str)) return false;
    return true;
}

function validateSpecialChars($str) {
    if (!validateOSInjection($str)) return false;
    return true;
}

function validateOSInjection($val) {
    if (preg_match('|^[^<>"\';]*$|', $val) && !preg_match('/SLEEP|waitfor|delay|benchmark/i', $val)) {
        return true;
    } else {
        error_log('OS Injection pattern detected: ' . $val);
        return false;
    }
}

function filter($data) {
    if (is_array($data)) {
        foreach ($data as $key => $element) {
            $data[$key] = filter($element);
        }
    } else {
        $data = trim(htmlentities(strip_tags($data)));
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) $data = stripslashes($data);
        $data = addslashes($data); // Use addslashes instead of mysql_real_escape_string
    }
    return $data;
}
?>
