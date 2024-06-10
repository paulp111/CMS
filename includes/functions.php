<?php
function e(string $string): string
{
    return htmlentities($string, ENT_QUOTES, "UTF-8", false);
}

function pdo_execute(PDO $pdo, string $sql, array $bindings = null): false|PDOStatement
{
    if (!$bindings) {
        return $pdo->query($sql);
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($bindings);

    return $statement;
}

function format_date(string $string): string
{
    $date = date_create_from_format('Y-m-d H:i:s', $string);

    return $date->format('d M. Y');
}
