<?php defined('SYSPATH') OR die('No direct access allowed.');

class Emailqueuelog extends Model
{

	protected $ids;
	protected $limit = 100;
	protected $offset;

	public function get()
	{
		$sql = 'SELECT * FROM email_queue WHERE 1';

		if ($this->ids) $sql .= ' AND id IN ('.implode(',', $this->ids).')';

		$sql .= ' ORDER BY queued DESC';

		if ($this->limit)
		{
			$sql .= ' LIMIT '.$this->limit;

			if ($this->offset) $sql .= ' OFFSET '.$this->offset;
		}

		return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function get_count()
	{
		$sql = 'SELECT COUNT(id) FROM email_queue WHERE 1';

		if ($this->ids) $sql .= ' AND id IN ('.implode(',', $this->ids).')';

		return $this->pdo->query($sql)->fetchColumn();
	}

	public function ids($array = NULL)
	{
		if ($array === NULL) $this->ids = NULL;
		else
		{
			if ( ! is_array($array)) $array = array($array);

			array_map('intval', $array);

			$this->ids = $array;
		}

		return $this;
	}

	public function limit($num)
	{
		if ($num === NULL) $this->limit = NULL;
		else
		{
			$this->limit = preg_replace('/[^0-9]+/', '', $num);
			if ($this->limit == '') $this->limit = NULL; // No limit found
		}

		return $this;
	}

	public function offset($num)
	{
		if ($num === NULL) $this->offset = NULL;
		else
		{
			$this->offset = preg_replace('/[^0-9]+/', '', $num);
			if ($this->offset == '') $this->offset = NULL; // No limit found
		}

		return $this;
	}

}