<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Sendemails extends Controller
{

	public function action_index()
	{
		header('Content-Type: text/plain');
		$emailqueue = new Emailqueue;
//		echo 'Added: '.$emailqueue->add('hurbel@yahoo.com', 'Liten body', 'litet subject');
		print_r($emailqueue->send());
		echo "\n";
		echo "All done";
	}

}
