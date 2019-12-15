<?php

function getRandomPassword()
{
	$CharSet = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP@$!%*?&';
	$max = 10;
	$size = StrLen($CharSet)-1;
	$password = array();

    while($max--)
    $password[] = $CharSet[rand(0, $size)];

	while(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/', implode($password), $matches))
	$password[rand(0, $max-1)] = $CharSet[rand(0, $size)];

	return implode($password);
}


function checkReCaptcha()
{
	global $errors;
	global $data;

	if (!empty($data['g-recaptcha-response']))
	{ 	
		$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.SECRETKEY.
			'&response='.$data['g-recaptcha-response'].
			'&remoteip='.$_SERVER['REMOTE_ADDR']);

		$response = json_decode($response, TRUE); 
		if ($response['success'] !== TRUE)
		{ 
			$errors[] = array('Invalid server response (2)!');
		}
	} else
	{
		$errors[] = array('Invalid server response (1)!');
	}
}
