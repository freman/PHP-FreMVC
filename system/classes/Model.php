<?php
namespace MVC;
/**
 * Model
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @package FreMVC
 * @version 0.1
 * @since 0.1
 * @copyright Fremnet.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * FreMVC Model class
 *
 * Base class for all models
 *
 * Tables expecting to use this class should be defined as
 *
 * table_name {
 * 	table_name_id
 * 	table_name_fielda
 * 	table_name_fieldb
 * }
 *
 * I reserve the right to rewrite this from scratch...
 *
 * @author Shannon Wynter <http://fremnet.net/contact>
 * @version 0.1
 * @since 0.1
 */
class Model {
	protected $app;
	protected $db;
	protected $name;
	protected $fields = array();

	/**
	 * Full field name
	 *
	 * Will prefix $table_name to the given field name unless it thinks
	 * $field_name exists (or is defined in $this->fields)
	 *
	 * @param string $name of the field
	 * @return string
	 */
	public function _fieldName($name) {
		return in_array($name, $this->fields) || strpos($name, $this->name . '_') === 0 ? $name : $this->name . '_' . $name;
	}

	/**
	 * Constructor
	 *
	 * Build the class
	 */
	public function __construct($app) {
		$this->app = $app;
		$this->db = $app->db();
		if (!$this->name) {
			// Extrapolate the table name from the class
			$parts = explode('_', get_class($this), 2);
			$this->name = strtolower($parts[1]);
		}
	}

	/**
	 * Find
	 *
	 * Takes any of the following
	 *  * column name & value
	 *  * array of column names & values
	 *
	 * Examples:
	 * 	find('name', 'fred');
	 * 	find(array(
	 * 		'first_name' => 'fred',
	 * 		'last_name'  => 'flintstone'
	 * 	));
	 * 	find(array('some_value' =>  array('>', 10)));
	 *
	 * @todo add or support
	 *
	 * @param mixed $name either the column name or an array
	 * @param mixed $value the value for a simple lookup
	 * @return PDOStatement
	 */
	public function find($name, $value = null) {
		$sql = 'SELECT * FROM `' . $this->name . '` WHERE ';

		// Convert a string name to an array anyway so all the processing is identical
		if (!(is_array($name) && is_null($value)))
			$name = array($name => $value);

		$bits = array();
		foreach ($name as $n => &$v) {
			$fn = $this->_fieldName($n);
			if (is_array($v) && isset($v[0]) {
				$bits[] = '`' . $fn . '` ' . $v[0] . ' ?';
				$v=$v[1];
			}
			else {
				$bits[] = '`' . $fn . '` = ?';
			}
		}
		$sql .= join(' AND ', $bits);

		$stmt = $this->db->prepare($sql);

		if ($stmt->execute(array_values($name)))
			return $stmt;

		throw new Exception('Unable to find ' . $value . ' in ' . $name);
	}

	/**
	 * Find one and only one
	 *
	 * @see find()
	 *
	 * @return mixed named array or false
	 */
	public function findOne($name, $value = null) {
		$stmt = $this->find($name, $value);
		if ($row = $stmt->fetch())
			return $row;
		return false;
	}

	/**
	 * Find all
	 *
	 * @see find()
	 *
	 * @return mixed array or false
	 */
	public function findAll($name, $value = null) {
		$stmt = $this->find($name, $value);
		if ($rows = $stmt->fetchAll())
			return $rows;
		return false;
	}

	/**
	 * Load
	 *
	 * Load a given record by ID
	 *
	 * @param integer $id
	 * @return mixed named array or false
	 */
	public function load($value) {
		return $this->findOne('id', $value);
	}

	/**
	 * Insert (on duplicate key update)
	 *
	 * Insert a record in to the table.
	 * Optionally iupdate an existing record if there is a duplicate key.
	 *
	 * @param array $data to store
	 * @param array $update optional list of fields from $data to update if there is a duplicate key
	 * @return integer
	 */
	public function insert($data, array $update = array()) {
		$fields = array_map(array($this, '_fieldName'), array_keys($data));
		$values = array_values($data);
		$question_marks = array_fill(0, count($values), '?');

		$sql = 'INSERT INTO `' . $this->name . '` (`' . join('`, `', $fields) . '`) VALUES (' . join(', ', $question_marks) . ')';

		if (!empty($update)) {
			$sql .= ' ON DUPLICATE KEY UPDATE `' . join('` = ?, `', $update) . '` = ?';
			$values = array_merge($values, array_intersect_key($data, $update));
		}

		$stmt = $this->db->prepare($sql);
		if ($stmt->execute($values))
			return $this->db->lastInsertId();
	}

	/**
	 * Remove a record
	 *
	 * Pass a named array as $id to remove by something other than ID
	 *
	 * Example:
	 * 	remove(10);
	 * 	remove(array('name' => 'barny'));
	 *
	 * @param mixed $id integer or array
	 * @return true on success
	 */
	public function remove($id) {
		$sql = 'DELETE FROM `' . $this->name . '` WHERE ';
		if (!is_array($id))
			$id = array($this->name . '_id' => $id);

		$bits = array();
		foreach ($id as $n => $v) {
			$bits[] = '`' . $this->_fieldName($n) . '` = ?';
		}
		$sql .= join(' and ', $bits);

		$stmt = $this->db->prepare($sql);
		if ($stmt->execute(array_values($id)))
			return true;
	}

	/**
	 * Update a record
	 *
	 * Pass a named array as $id to update by something other than ID
	 *
	 * Example:
	 * 	update(10, $data);
	 * 	update(array('name' => 'barny'), $data);
	 *
	 * @param mixed $id integer or array
	 * @param array $data updated data
	 * @return true on success
	 */
	public function update($id, array $data) {
		$sql = 'UPDATE `' . $this->name . '` SET ';

		$bits = array();
		foreach ($data as $n => $v) {
			$bits[] = '`' . $n . '` = ?';
		}
		$sql .= join(', ', $bits);

		$sql .= ' WHERE ';

		if (!is_array($id))
			$id = array($this->name . '_id' => $id);

		$bits = array();
		foreach ($id as $n => $v) {
			$bits[] = '`' . $this->_fieldName($n) . '` = ?';
		}
		$sql .= join(' and ', $bits);

		$stmt = $this->db->prepare($sql);

		if ($stmt->execute(array_merge(array_values($fields), array_values($id))))
			return true;
	}
}
