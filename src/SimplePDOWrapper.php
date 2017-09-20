<?php

/**
 * Simple PDO wrapper to make it easier
 * to fetch data from a MYSQL database and simplify/beautify sql syntax.
 */
class SimplePDOWrapper
{
	/**
	 * Main DB variable holds PDO instance.
	 *
	 * @var object
	 */
	protected $db = null;

	/**
	 * Errors template.
	 *
	 * @var array
	 */
	protected $_errors = array(
		'code' => null,
		'message' => null
	);

	/**
	 * Errors in general.
	 * (Public version).
	 *
	 * @var null
	 */
	public $errors = null;

	/**
	 * Constructor.
	 * Checks for important stuff before constructing the instance.
	 *
	 * @param  array $db_credentials Mysql credentials for login.
	 * @return void
	 */
	public function __construct($conf = array())
	{
		if (!class_exists('PDO'))
		{
			throw new ErrorException('PDO Class is missing in PHP!');
		}

		$this->setDatabase($conf);
	}

	/**
	 * If you feel like changing database on the fly just
	 * pass credentials.
	 *
	 * @param  array $options Should contain array of credentials.
	 * @return boolean
	 */
	public function setDatabase($conf = array())
	{
		$connected = false;

		try
		{
			if (!$conf)
			{
				throw new InvalidArgumentException('Configuration arguments are missing!');
			}

			$db       = (string) $conf['database'];
			$host     = (string) $conf['host'];
			$user     = (string) $conf['user'];
			$password = (string) $conf['password'];

			if (!$this->connect($db, $user, $password, $host))
			{
				throw new InvalidArgumentException('Database could not connect with given credentials.');
			}

			$connected = true;
		}
		catch (Exception $e)
		{
			$this->errors = array(
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			) + $this->_errors;
		}
		finally
		{
			return $connected;
		}
	}

	/**
	 * Method to instantiate {this}{DB} with given credentials.
	 *
	 * @param  string $db       Database name.
	 * @param  string $user     Database username.
	 * @param  string $password Database username password.
	 * @param  string $host     Database host.
	 * @return boolean
	 */
	private function connect($db, $user, $password, $host = 'localhost')
	{
		$this->db = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

		return !!$this->db;
	}

	/**
	 * Saves a new entity.
	 * This method will remove auto increment keys and
	 * require schema's fields to avoid obvious PDO exceptions.
	 *
	 * @param  string $entity Entity to save into.
	 * @param  array  $data   Entity's data to save.
	 * @throws Exception      About not being able to save data.
	 * @return array  $result Array or the new entry.
	 */
	public function save($entity, $data = array())
	{
		$result = null;
		try
		{
			$schema = self::getTableSchema($entity);
			foreach ($data as $field => $value)
			{
				if (isset($schema[$field]) && $schema[$field]['extra'] == 'auto_increment')
				{
					unset($data[$field]);
				}
			}

			$this->db->beginTransaction();

			$transaction = $this->db->prepare(self::buildQuery($entity, 'save', $data, array()));
			if (!$transaction->execute())
			{
				throw new Exception('Data could not be saved :/');
			}

			$result = $this->findOne($entity, array(
				'conditions' => array(
					'id' => $this->db->lastInsertId()
				)
			));

			$this->db->commit();
		}
		catch (Exception $e)
		{
			$this->errors = array(
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			) + $this->_errors;

			$this->db->rollback();
		}
		finally
		{
			return $result;
		}
	}

	/**
	 * Updates an entity.
	 * This method will require schema's fields to avoid obvious
	 * PDO exceptions.
	 *
	 * @param  string  $entity  Entity to save into.
	 * @param  array   $data    Entity's data to save.
	 * @throws Exception        About not being able to save data.
	 * @return boolean $result  Null or the new entry.
	 */
	public function update($entity, $data = array(), $conditions = array())
	{
		$result = null;
		try
		{
			if (!$conditions)
			{
				throw new Exception('No conditions where given. Can\'t update blidly');
			}

			$schema = self::getTableSchema($entity);

			foreach ($schema as $field => $description)
			{
				if (!isset($data[$field]) && $description['extra'] == 'auto_increment' || $description['key'] == 'PRI')
				{
					unset($data[$field]);
				}
			}

			$this->db->beginTransaction();

			$transaction = $this->db->prepare(self::buildQuery($entity, 'update', $data, $conditions));
			if (!$result = $transaction->execute())
			{
				throw new Exception('Data could not be saved :/');
			}

			$this->db->commit();
		}
		catch (Exception $e)
		{
			$this->errors = array(
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			) + $this->_errors;

			$this->db->rollback();
		}
		finally
		{
			return $result;
		}
	}

	/**
	 * Delete a record or many from a given entity (table).
	 *
	 * @param  string $entity  Table name.
	 * @param  array  $options Conditions container.
	 * @return boolean
	 */
	public function delete($entity, $options = array())
	{
		$result = false;
		try
		{
			if (!$options || !$options['conditions'])
			{
				throw new InvalidArgumentException('Options conditions are missing!');
			}

			$transaction = $this->db->prepare(self::buildQuery($entity, 'delete', array(), $options));
			$result = $transaction->execute();
		}
		catch (Exception $e)
		{
			$this->errors = array(
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			) + $this->_errors;
		}
		finally
		{
			return $result;
		}
	}

	/**
	 * Deletes all records from a given entity (table).
	 *
	 * @param  string $entity Table name.
	 * @return boolean
	 */
	public function deleteAll($entity)
	{
		$result = false;
		try
		{
			$transaction = $this->db->prepare(self::buildQuery($entity, 'delete_all', array()));
			$result = $transaction->execute();
		}
		catch (Exception $e)
		{
			$this->errors = array(
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			) + $this->_errors;
		}
		finally
		{
			return $result;
		}
	}

	/**
	 * Search for only ONE record from a given entity.
	 *
	 * @param  string $entity  Database table.
	 * @param  array  $options Query options like 'conditions', 'limit', 'order', etc.
	 * @param  bool   $assoc   Return associative array or stdclass object.
	 * @throws Exception       Entity was not specified.
	 * @return mixed
	 */
	public function findOne($entity, $options = array(), $assoc = true)
	{
		$result = false;
		try
		{
			$transaction = $this->db->prepare(self::buildQuery($entity, 'find_one', array(), $options));
			$transaction->execute();

			$result = $transaction->fetchAll($assoc ? PDO::FETCH_ASSOC : PDO::FETCH_OBJ);
		}
		catch (Exception $e)
		{
			$this->errors = array(
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			) + $this->_errors;
		}
		finally
		{
			return $result ? $result[0] : array();
		}
	}

	/**
	 * Search for many records from a given entity.
	 *
	 * @param  string $entity  Database table.
	 * @param  array  $options Query options like 'conditions', 'limit', 'order', etc.
	 * @param  bool   $assoc   Return associative array or object class.
	 * @throws Exception       Entity was not specified.
	 * @return mixed
	 */
	public function findAll($entity, $options = array(), $assoc = true)
	{
		$result = false;
		try
		{
			$transaction = $this->db->prepare(self::buildQuery($entity, 'find_all', array(), $options));
			$transaction->execute();

			$result = $transaction->fetchAll($assoc ? PDO::FETCH_ASSOC : PDO::FETCH_OBJ);
		}
		catch (Exception $e)
		{
			$this->errors = array(
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			) + $this->_errors;
		}
		finally
		{
			return $result ? $result : array();
		}
	}

	/**
	 * Get a table's schema.
	 *
	 * @param  string $table  The table we want to extract the schema from.
	 * @return mixed  $schema Null or schema array.
	 */
	private function getTableSchema($table)
	{
		$schema = array();

		$transaction = $this->db->prepare("SHOW COLUMNS FROM `$table`");
		$transaction->execute();

		foreach ($transaction->fetchAll(PDO::FETCH_ASSOC) as $field)
		{
			$schema[$field['Field']] = array_change_key_case($field, CASE_LOWER);
			unset($schema[$field['Field']]['Field']);
		}

		return $schema;
	}

	/**
	 * Builds a query from options.
	 * Currently supports:
	 *	· Insert
	 *	· Update
	 *	· Find one
	 *	· Find all (with conditions and stuff)
	 *
	 * @param  string  $table   Entity table.
	 * @param  string  $action  Action to perform (save, find, etc).
	 * @param  array   $data    The data to manipulate in case of save or update.
	 * @param  array   $options Containing available clauses (conditions, order, fields, limit, where).
	 * @return string  $query   Query built.
	 */
	private function buildQuery($table, $action = 'save', $data, $options = array())
	{
		$query  = '';
		$action = strtolower($action);
		$insert = '';
		$where  = '';
		$limit  = isset($options['limit']) && $options['limit'] ? "LIMIT {$options['limit']}" : '';
		$fields = isset($options['fields']) && $options['fields'] ? implode(',', $options['fields']) : '*';
		$order  = isset($options['order']) && $options['order'] ? 'ORDER BY '.implode(', ', $options['order']) : '';

		if (isset($options['conditions']) && $options['conditions'])
		{
			foreach ($options['conditions'] as $field => $condition)
			{
				$where .= " $field='$condition'";
			}

			if ($where)
			{
				$where = 'WHERE'.$where;
			}
		}

		if ($action == 'save')
		{
			$fields = array();
			$values = array();

			foreach ($data as $field => $value)
			{
				$fields[] = filter_var($field, FILTER_SANITIZE_STRING);
				$values[] = filter_var($value, FILTER_SANITIZE_STRING);
			}

			$insert = '('.implode(', ', $fields).') VALUES (\''.implode('\', \'', $values).'\')';
			$query  = "INSERT INTO $table $insert";
		}
		elseif ($action == 'update')
		{
			$update = 'SET ';
			foreach ($data as $field => $value)
			{
				$field = filter_var($field, FILTER_SANITIZE_STRING);
				$value = filter_var($value, FILTER_SANITIZE_STRING);
				$update .= "$field='$value', ";
			}

			$update = preg_replace('/,\s+$/', '', $update);

			$query  = "UPDATE $table $update $where";
		}
		elseif ($action == 'find_one')
		{
			$query = "SELECT $fields FROM $table $where $order LIMIT 1";
		}
		elseif ($action == 'find_all')
		{
			$query = "SELECT $fields FROM $table $where $order $limit";
		}
		elseif ($action == 'delete')
		{
			$query = "DELETE FROM $table $where";
		}
		elseif ($action == 'delete_all')
		{
			$query = "DELETE FROM $table"
		}

		return (string) $query;
	}
}
