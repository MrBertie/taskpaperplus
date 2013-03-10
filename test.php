<?php
namespace tpp;
require_once('inc/init.php');

// Include the test framework
include('tests/EnhanceTestFramework.php');

// Find the tests - '.' is the current folder
\Enhance\Core::discoverTests('tests/', false);

// Run the tests
\Enhance\Core::runTests();