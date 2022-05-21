<?php

/**
 * PROJECT URLs
 */
define("URL_DEVELOPMENT", "http://localhost/controlefinanceiro");

/**
 * SITE CONFIG
 */
define("SITE", [
    "name" => "Lançamento de Contas",
    "desc" => "Lança, Edita e Remove Receitas e Despesas",
    "domain" => "www.com.br",
    "locale" => "pt_BR",
    "root" => URL_DEVELOPMENT
]);

define(
    "DATA_LAYER_CONFIG",
    [
        "driver" => "mysql",
        "host" => "localhost",
        "port" => "3306",
        "dbname" => "controle_financeiro",
        "username" => "root",
        "passwd" => "",
        "options" => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ]
    ]
);
