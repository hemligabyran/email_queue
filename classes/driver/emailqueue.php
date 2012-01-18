<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Driver_Emailqueue extends Model
{

	public function __construct()
	{
		parent::__construct();
		if (Kohana::$environment == Kohana::DEVELOPMENT)
		{
			if ( ! $this->check_db_structure())
			{
				$this->create_db_structure();
			}
		}
	}

	/**
	 * Returns true/false depending on if the db structure exists or not
	 *
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 * @return boolean
	 */
	abstract protected function check_db_structure();

	/**
	 * Create the db structure
	 *
	 * @return boolean
	 */
	abstract protected function create_db_structure();

	/**
	 * Add mail to queue
	 *
	 * @param str $to_email
	 * @param str $body
	 * @param str $subject      OPTIONAL
	 * @param str $to_name      OPTIONAL
	 * @param str $attachments  OPTIONAL BASE64!!!
	 * @param str $from_email   OPTIONAL - Will use setting in config file if not supplied
	 * @param str $from_name    OPTIONAL - Will use setting in config file if not supplied
	 * @return int ID in database
	 */
	abstract public function add($to_email, $body, $subject, $to_name, $attachments, $from_email, $from_name);

	/**
	 * Send emails from the queue
	 *
	 * @param int $amount - amount of emails to send. Dont set this to high. Maybe 5-10 is good
	 * @return array with sucesses
	 */
	abstract public function send($amount);

}