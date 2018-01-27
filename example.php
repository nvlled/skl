
<?php

include "skl.php";

//skl_database("skl");
//skl_hostname("localhost");
//skl_username("nvlled");
//skl_password("");
skl_connect("localhost", "nvlled", "", "skl");

skl_pagesize(3);
skl_exec("
    create table if not exists messages(
        id integer auto_increment primary key,
        username varchar(255),
        contents text
    )
");

$pagenum = @$_REQUEST["page"]+0;
skl_pagenum($pagenum);

$newId = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = @$_POST["action"];
    $username = $_POST["username"];
    $contents = $_POST["contents"];
    if ($action == "clear") {
        skl_exec("delete from messages");
    } else {
        skl_exec("
            insert into messages(username, contents)
            values(?, ?)
        ", $username, $contents);
        $newId = skl_insert_id();
    }
}

$msgcount = skl_get("select count(*) from messages");
$pagecount = $msgcount / skl_pagesize();

?>

<?php if ($newId != NULL) { ?>
<p>last insert ID: <?=$newId?></p>
<?php } ?>

<h2>Messages</h2>
<ul>
<?php if ($msgcount <= 0) { ?>
    <em>no messages posted</em>
<?php } ?>
<?php foreach(skl_all("select * from messages") as $message) { ?>
    <li>
        <?=$message["id"]?>
        <pre><?= $message["username"] ?>: <?= $message["contents"] ?></pre>
    </li>
<?php } ?>
</ul>


<div class='nav'>
page:
<?php for ($i = 0; $i < $pagecount; $i++) {
    if ($pagenum == $i) {
        echo "<a class='sel' href='?page=$i'>".($i+1)."</a>";
    } else {
        echo "<a href='?page=$i'>".($i+1)."</a>";
    }
} ?>
</div>


<h3>Add Message</h3>
<form method="POST">
    <input name="username" placeholder="your name">
    <br>
    <textarea name="contents" rows='8' cols='50'
              placeholder='your message'></textarea>
    <br>
    <button>submit</button>
    <button name='action' value='clear'>clear messages</button>
</form>
<style>
.nav a {
    padding-right: 10px;
    text-decoration: none;
}
.nav a.sel {
    color: green;
    text-decoration: overline;
}

</style>
