<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Emaillog extends Admincontroller
{

	public function before()
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/emaillog';
		xml::to_XML(array('admin_page' => 'Email log'), $this->xml_meta);
	}

	public function action_index()
	{
		$page = 1;

		if (isset($_GET['page']))
			$page = intval($_GET['page']);

		$emailqueue                   = Emailqueue::factory();
		$count                        = $emailqueue->get_count_emails();

		if ($page < 1) $page          = 1;

		$limit                        = 10;
		$offset                       = ($page-1)*$limit;
		if ($offset > $count) $offset = 0;
		$pages                        = ceil($count/$limit);
		$actual_page                  = floor($offset/$limit)+1;

		$emails                       = $emailqueue->get_emails(NULL, $limit, $offset);

		xml::to_XML($emails, array('emails' => $this->xml_content), 'email', 'id');
		xml::to_XML(array('email_count' => $count, 'pages'=>$pages, 'actual_page'=>$actual_page, 'limit'=>$limit), array('mails_meta'=>$this->xml_content));
	}

}