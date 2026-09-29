<?php 
	$token = NULL;
	if (isset($_GET['token']))
	{
		$token = $_GET['token'];
	}
	$chiper = date('Y-m-d H');
	$chiper = md5($chiper).'@'.date('Y');

	if ($chiper === $token)
	{
		echo phpinfo();
		echo date('Y-m-d H:i:s');
	}
	else
	{
		header('HTTP/1.1 404 Page Not Found.', TRUE, 404);
		echo 'The page request is not found.';
		exit(1);
	}
?>