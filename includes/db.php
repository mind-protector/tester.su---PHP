<?php
require 'libs/rb.php';

R::setup('mysql:host=localhost;dbname=tester', 'root');
session_start();

const SECRETKEY = '6LcyjscUAAAAAOsnqWDkLnjAKWY2mY8tkb7z69zc';
