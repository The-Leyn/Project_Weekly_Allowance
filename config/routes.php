<?php

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * Configuration des routes de l'application
 */
return simpleDispatcher(function (RouteCollector $r) {
  // Routes publiques (pas d'authentification requise)
  $r->addRoute('POST', '/api/auth/register', ['AuthController', 'register']);
  $r->addRoute('POST', '/api/auth/login', ['AuthController', 'login']);

  // Routes protégées (authentification JWT requise)
  $r->addRoute('POST', '/api/auth/logout', ['AuthController', 'logout']);

  // Routes Wallet
  $r->addRoute('POST', '/api/wallet', ['WalletController', 'create']);
  $r->addRoute('GET', '/api/wallet/{userId:\d+}', ['WalletController', 'getByUserId']);
  $r->addRoute('POST', '/api/wallet/deposit', ['WalletController', 'deposit']);
  $r->addRoute('POST', '/api/wallet/expense', ['WalletController', 'recordExpense']);
  $r->addRoute('PUT', '/api/wallet/allowance', ['WalletController', 'setAllowance']);
  $r->addRoute('POST', '/api/wallet/apply-allowance', ['WalletController', 'applyAllowance']);

  // Routes de vues (optionnel, pour l'interface web)
  $r->addRoute('GET', '/', ['ViewController', 'home']);
  $r->addRoute('GET', '/dashboard', ['ViewController', 'dashboard']);
});
