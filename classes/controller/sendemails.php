<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Sendemails extends Controller
{

	public function action_index()
	{
		header('Content-Type: text/plain');
		$emailqueue = new Emailqueue;
		foreach ($emailqueue->send() as $mail)
		{
			echo 'ID: '.$mail['id'].' Status: '.$mail['status'].' Email: '.$mail['email']."\n";
		}
		die("\n".'End');
	}

}
