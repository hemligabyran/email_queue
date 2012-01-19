<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Emailqueue_Mysql extends Driver_Emailqueue
{

	protected static $prepared_insert;

	protected function check_db_structure()
	{
		$tables = $this->pdo->query('SHOW TABLES;')->fetchAll(PDO::FETCH_COLUMN);
		if (in_array('email_queue', $tables))
		{
			$columns = $this->pdo->query('DESCRIBE email_queue;')->fetchAll(PDO::FETCH_COLUMN);

			if (
				$columns == array(
					'id',
					'status',
					'attempts',
					'to_email',
					'to_name',
					'from_email',
					'from_name',
					'subject',
					'body',
					'attachments',
					'queued',
					'sent',
					'last_attempt'
				)) return TRUE;
		}

		return FALSE;
	}

	protected function create_db_structure() {
		return $this->pdo->query('CREATE TABLE IF NOT EXISTS `email_queue` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`status` enum(\'queue\',\'sent\',\'failed\') DEFAULT \'queue\',
			`attempts` int(11) unsigned NOT NULL,
			`to_email` varchar(255) NOT NULL,
			`to_name` varchar(255) DEFAULT NULL,
			`from_email` varchar(255) DEFAULT NULL,
			`from_name` varchar(255) DEFAULT NULL,
			`subject` varchar(255) DEFAULT NULL,
			`body` text NOT NULL,
			`attachments` text,
			`queued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`sent` timestamp,
			`last_attempt` timestamp,
			PRIMARY KEY (`id`),
			KEY `status` (`status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
	}

	public function add($to_email, $body, $subject, $to_name, $attachments, $from_email, $from_name)
	{
		if ( ! self::$prepared_insert)
		{
			$sql = 'INSERT INTO email_queue (to_email, body, subject, to_name, attachments, from_email, from_name) VALUES(?,?,?,?,?,?,?);';

			self::$prepared_insert = $this->pdo->prepare($sql);
		}

		if (Valid::email($to_email) && Valid::email($from_email))
		{
			self::$prepared_insert->execute(array($to_email, $body, $subject, $to_name, $attachments, $from_email, $from_name));
			return $this->pdo->lastInsertId();
		}
		else return FALSE;
	}

	public function send($amount)
	{
		$statuses  = array();
		$failed    = array();
		$successed = array();
		$mails     = $this->pdo->query('SELECT * FROM email_queue WHERE status = \'queue\' ORDER BY queued LIMIT '.intval($amount).';')->fetchAll(PDO::FETCH_ASSOC);
		foreach ($mails as $mail)
		{
			$mail_response = (bool) Email::factory($mail['subject'], $mail['body'])
				->to($mail['to_email'], $mail['to_name'])
				->from($mail['from_email'], $mail['from_name'])
				->send($errors);

			if ($mail_response)
			{
				$statuses[]  = array('id' => $mail['id'], 'email' => $mail['to_email'], 'status' => 'sent');
				$successed[] = $mail['id'];
			}
			else
			{
				$statuses[] = array('id' => $mail['id'], 'email' => $mail['to_email'], 'status' => 'failed');
				$failed[]   = $mail['id'];
			}

		}

		if (count($failed))    $this->pdo->query('UPDATE email_queue SET status = \'failed\', attempts = attempts + 1, last_attempt = NOW() WHERE id IN ('.implode(',',$failed).');');
		if (count($successed)) $this->pdo->query('UPDATE email_queue SET status = \'sent\',   attempts = attempts + 1, last_attempt = NOW(), `sent` = NOW() WHERE id IN ('.implode(',',$successed).');');

		return $statuses;
	}

}
