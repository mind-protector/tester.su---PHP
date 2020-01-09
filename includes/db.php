<?php

/**
 * Tester.su database connect and session start.
 */

require 'libs/rb.php';

R::setup('mysql:host=localhost;dbname=tester', 'root');
session_start();
