# skl

a beginner-friendly API for database access in PHP

## rationale

To this date, I still see lots of people use the deprecated mysql_* function, 
coupled with the horrible concatenation/interpolation on query strings. 
I wonder why people still use the defunct API when there are better 
alternatives such as [PDO](http://php.net/manual/en/book.pdo.php), 
or other stable ORMs or query builders.

But if I have to guess, I think the reasons are

* PDO uses OOP constructs which repels people who are used to imperative code
* Libraries of ORMs or query builders require some tooling to setup
* Dated instruction materials still refer to the deprecated mysql_ API

skl tries at least to address these issues by having a simple, imperative API
that (sort of) discourages concatenation/interpolation on query strings with
little or no setup or tooling required.

## setup

1. Download and place the [file](https://raw.githubusercontent.com/nvlled/skl/master/skl.php)
in your project directory
2. Include the file in your php pages by adding ```include "skl.php";```

## configuration
Accessing your database from PHP always require a bit of configuration.
To configure your database credentials, simply invoke the following functions:

```php
skl_hostname("localhost");
skl_database("your_dbname");
skl_username("your_username");
skl_password("your_password");
```

## creating tables
Creating tables can be done by using skl_exec:
```php
skl_exec("
    create table if not exists messages(
        id integer auto_increment primary key,
        username varchar(255),
        contents text
    )
");
```


## querying
After configuration, you can now make queries. To query rows, simply use the skl_all:
```php
foreach(skl_all("select * from messages") as $message) {
  echo "{$message['username'] : {$message['contents']}<br>";
}
```


To query a single row only, use skl_one:
```php
$id = $_POST["id"];
$message = skl_one("select * from message where id = ?", $id);
echo "{$message['username'] : {$message['contents']}<br>";
```

## deleting and inserting
To delete use the skl_exec:
```php
$id = 1;
skl_exec("delete * from messages"); // delete all rows from the table
skl_exec("delete * from messages"); // delete a row from the table with id = $id
skl_exec("delete * from message where id = ?", $id);
```
Insertion is also done with skl_exec:
```php
$username = "testing";
$contents = "hello world";
skl_exec("insert into message(username, contents)  values(?, ?)", $username, $contents);

```
## examples
Documentation and more examples will be added later, but for now
a complete working example can be found in the [example.php](example.php) file.

## caveats 
Although skl is just a thin layer over PDO, skl is mainly intended for educational use.
Use with discretion.
