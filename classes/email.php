<?php defined('SYSPATH') or die('No direct script access.');

class Email extends Kohana_Email
{

	// Todo: fetch these from the swift library
	public $target_addresses = array();

	/**
	 * Send the email.
	 *
	 * !! Failed recipients can be collected by using the second parameter.
	 *
	 * @param   array    failed recipient list, by reference
	 * @return  integer  number of emails sent
	 */
	public function send(array &$failed = NULL)
	{
		if (Kohana::$environment === Kohana::PRODUCTION)
		{
			return parent::send($failed);
		}
		else
		{
			$allowed_test_emails = Kohana::$config->load('emailqueue.allowed_test_emails');

			$all_addresses_ok = TRUE;
			foreach ($this->target_addresses as $target_address)
				if ( ! in_array($target_address, $allowed_test_emails))
					$all_addresses_ok = FALSE;

			if ( ! $allowed_test_emails || ! $all_addresses_ok)
			{
				Log::instance()->add(Log::DEBUG, 'Faked sending email to :'.serialize($this->target_addresses));

				return 1;
			}
			else
			{
				return parent::send($failed);
			}
		}
	}

	/**
	 * Add one or more email recipients..
	 *
	 *     // A single recipient
	 *     $email->to('john.doe@domain.com', 'John Doe');
	 *
	 *     // Multiple entries
	 *     $email->to(array(
	 *         'frank.doe@domain.com',
	 *         'jane.doe@domain.com' => 'Jane Doe',
	 *     ));
	 *
	 * @param   mixed    single email address or an array of addresses
	 * @param   string   full name
	 * @param   string   recipient type: to, cc, bcc
	 * @return  Email
	 */
	public function to($email, $name = NULL, $type = 'to')
	{
		$this->target_addresses[] = $email;

		return parent::to($email, $name, $type);
	}

}