<?php

use CoffeeCode\Router\Router;

require __DIR__ . "/vendor/autoload.php";

$router = new Router(site());

$router->namespace("Source\Controllers\App");
$router->get("/", "FinancialView:home", "financialview.home");
$router->get("/receitas", "FinancialView:incomes", "financialview.incomes");
$router->get("/despesas", "FinancialView:expenses", "financialview.expenses");
$router->get("/despesas/{filter}", "FinancialView:expenses", "financialview.expenses");
$router->post("/create-invoice", "FinancialAuth:createInvoice", "financialauth.createinvoice");
$router->post("/edit-invoice/{id}", "FinancialAuth:editInvoice", "financialauth.editinvoice");
$router->post("/remove-invoice/{id}", "FinancialAuth:removeInvoice", "financialauth.removeinvoice");
$router->group("ooops");
$router->get("/{errcode}", "FinancialView:nofFound", "financialview.notfound");

$router->dispatch();

if ($router->error()) {
    $router->redirect("financialview.notfound", ["errcode" => $router->error()]);
}
