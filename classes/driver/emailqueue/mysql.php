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

	public function add($to_email, $body, $subject, $to_name, $attachments, $from_email, $from_name, $send_directly)
	{
		if ( ! self::$prepared_insert)
		{
			$sql = 'INSERT INTO email_queue (to_email, body, subject, to_name, attachments, from_email, from_name, status) VALUES(?,?,?,?,?,?,?,?);';

			self::$prepared_insert = $this->pdo->prepare($sql);
		}

		if (Valid::email($to_email) && Valid::email($from_email))
		{
			if ($send_directly) $status = 'sent';
			else                $status = 'queue';

			self::$prepared_insert->execute(array($to_email, $body, $subject, $to_name, $attachments, $from_email, $from_name, $status));

			$email_id = $this->pdo->lastInsertId();

			if ($send_directly)
			{
				$mail_response = (bool) Email::factory($subject, $body, 'text/html')
					->to($to_email, $to_name)
					->from($from_email, $from_name)
					->send($errors);

				if ($mail_response)
					$this->pdo->exec('UPDATE email_queue SET attempts = attempts + 1, last_attempt = NOW(), `sent` = NOW() WHERE id  = '.$this->pdo->quote($email_id));
				else
					$this->pdo->exec('UPDATE email_queue SET status = \'failed\', attempts = attempts + 1, last_attempt = NOW() WHERE id  = '.$this->pdo->quote($email_id));
			}

			return $email_id;
		}
		else return FALSE;
	}

	public function send($amount)
	{
		$statuses  = array();
		$failed    = array();
		$successed = array();
		$this->pdo->exec('LOCK TABLES email_queue WRITE');
		$mails     = $this->pdo->query('SELECT * FROM email_queue WHERE status = \'queue\' ORDER BY queued LIMIT '.intval($amount).';')->fetchAll(PDO::FETCH_ASSOC);
		foreach ($mails as $mail)
		{
			$mail_response = (bool) Email::factory($mail['subject'], $mail['body'], 'text/html')
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

		$this->pdo->exec('UNLOCK TABLES');

		return $statuses;
	}

	public function get_emails($id=NULL, $limit=NULL, $offset=NULL)
	{
		$emails = array();

		$sql = 'SELECT * from email_queue WHERE 1=1';

		if ($id!=NULL)
		{
			$sql.=' AND id = '.$this->pdo->quote($id);
		}
		else
		{
			if ($limit !== NULL && $offset !== NULL)
			{
				$sql.=' LIMIT '.$offset.','.$limit;
			}
		}

		$query = $this->pdo->query($sql);

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$emails[$row['id']] = $row;
		}
		
		return $emails;
	}


	public function get_count_emails()
	{
		$emails = 0;

		$sql = 'SELECT count(*) as count from email_queue WHERE 1=1';

		$query = $this->pdo->query($sql);

		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$emails = intval($row['count']);
		}

		return $emails;
	}

}
