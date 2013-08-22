<?php defined('SYSPATH') OR die('No direct access allowed.');

class Emailqueue
{

	private static $driver;

	/**
	 * Set the database driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Emailqueue_'.ucfirst(Kohana::$config->load('emailqueue.driver'));
		return (self::$driver = new $driver_name);
	}

	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	public static function factory()
	{
		return new self();
	}

	// For docs, see classes/driver/emailqueue.php
	public function add($to_email, $body, $subject = NULL, $to_name = NULL, $attachments = NULL, $from_email = NULL, $from_name = NULL, $send_directly = FALSE)
	{
		if ( ! $from_email) $from_email = Kohana::$config->load('emailqueue.from_email');
		if ( ! $from_name)  $from_name  = Kohana::$config->load('emailqueue.from_name');

		return self::driver()->add($to_email, $body, $subject, $to_name, $attachments, $from_email, $from_name, $send_directly);
	}

	public function send($amount = 5)
	{
		return self::driver()->send($amount);
	}

}
