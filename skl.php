<?php
/*
Copyright (c) 2018 Ronald Casili

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

define("PAGESIZE", 256);

$dboptions = [
    "hostname"=>"localhost",
    "username"=>"root",
    "password"=>"",
    "database"=>"",
    "driver"=>"mysql",
    "pagesize"=>PAGESIZE,
    "pagenum"=>0,
];
$lastConnection;

$db = NULL;

function display_errors($errors) {
    echo "
        <style>
        #skl-errors {
            color: #fed;
            background-color: #333;
            font-size: 25px;
            padding: 20px;
            width: 80%;
            margin: 0 auto;
        }
        #skl-errors li {
            margin-left: 20px;
            padding: 10px;
        }
        </style>
    ";
    echo "<ul id='skl-errors'>
        <h1>SKL ERRORS</h1>
    ";
    foreach ($errors as $err) {
        echo "<li>$err</li>";
    }
    echo "</ul>";
}

function verify_connection() {
    global $dboptions;

    $errors = [];
    if ( ! @$dboptions["username"])
        $errors[] = "username is not set for database connection";
    if ( ! @$dboptions["database"])
        $errors[] = "database name is not specified";

    if ( @$dboptions["driver"] != "mysql")
        $errors[] = "database driver is invalid or not yet supported";

    if ($errors) {
        display_errors($errors);
        throw new Exception("DATABASE CONNECTION/EXECUTION FAILED");
    }
}

function skl_connect($options) {
    global $dboptions;
    $dboption = $options;
}

function skl_username($val) {
    global $dboptions;
    $dboptions["username"] = $val;
}

function skl_password() {
    global $dboptions;
    $dboptions["password"] = $val;
}

function skl_hostname($val) {
    global $dboptions;
    $dboptions["hostname"] = $val;
}

function skl_database($val) {
    global $dboptions;
    $dboptions["database"] = $val;
}

function skl_pagenum($val=NULL) {
    global $dboptions;
    if ($val != NULL) {
        $dboptions["pagenum"] = $val;
    }
    return $dboptions["pagenum"];
}

function skl_pagesize($val=NULL) {
    global $dboptions;
    if ($val != NULL) {
        $dboptions["pagesize"] = $val;
    }
    return $dboptions["pagesize"];
}

function create_connection() {
    verify_connection();

    global $dboptions;
    $opts = $dboptions;
    $dsn = "";
    if ($opts["driver"] == "mysql") {
        $dsn = "mysql:host={$opts['hostname']};dbname={$opts['database']}";
    }

    try {
        $conn = new PDO( $dsn, $opts['username'], $opts['password'] );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        if ($e->getCode() == 1045) {
            display_errors([
                "Access denied for `{$opts['username']}` on `{$opts['hostname']}`,
                 check your database username or password."
            ]);
        } else if ($e->getCode() == 2002) {
            display_errors([
                "Unable to connect to `{$opts['hostname']}`,
                 check if your database hostname or your network connection is
                 correct",
            ]);
        } else if ($e->getCode() == 1049) {
            display_errors([
                "{$opts['driver']} database `{$opts['database']}` does not exist.",
            ]);
        }
        throw new Exception("DATABASE CONNECTION/EXECUTION FAILED");
        //echo "<pre>";
        //$dbgt=debug_backtrace();
        //"$error in {$dbgt[1][file]} on line {$dbgt[1][line]}";
        //var_dump($e);
        //echo "</pre>";
    }
}

function skl_exec($sql, ...$params) {
    try {
        $conn = create_connection();
        $sth = $conn->prepare($sql);
        $sth->execute($params);
        global $lastConnection;
        $lastConnection = $conn;
        return $sth;
    } catch (PDOException $e) {
        $traces = $e->getTrace();
        $trace = $traces[count($traces)-1];
        display_errors([
            "You have an error on the file {$trace['file']}
            on line number {$trace['line']}",
            "Your query string is:<br> $sql",
            "Your query parameters are: " . implode(", ", $params),
            "{$e->getMessage()}",
        ]);
    }
}

function skl_one($sql, ...$params) {
    $sth = skl_exec($sql, ...$params);
    if (!$sth)
        return;
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$row)
        return NULL;
    return $row;
}

function skl_get($sql, ...$params) {
    $sth = skl_exec($sql, ...$params);
    if (!$sth)
        return;
    $row = $sth->fetch();
    if ($row) {
        return $row[0];
    }
    return NULL;
}

function skl_all($sql, ...$params) {
    global $dboptions;
    $pagenum = $dboptions["pagenum"];
    $pagesize = $dboptions["pagesize"];
    $offset = $pagenum * $pagesize;

    // wrap the query for pagination
    $sql = "select * from ($sql) t limit $offset, $pagesize";

    $sth = skl_exec($sql, ...$params);
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC); // what if there's a gazzillion rows

    return array_slice($rows, 0, PAGESIZE);
}

function skl_count_pages() {
    global $dboptions;
    $pagenum = $dboptions["pagenum"];
    $pagesize = $dboptions["pagesize"];
}

function skl_insert_id() {
    global $lastConnection;
    if ($lastConnection)
        return $lastConnection->lastInsertId();
    return NULL;
}

function skl_error() {
}

?>
