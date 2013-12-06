<?php
/**
 * @namespace
 */
namespace Engine\Acl\Adapter;

use Phalcon\Acl,
	Phalcon\Acl\Adapter,
	Phalcon\Acl\AdapterInterface,
	Phalcon\Acl\Role,
	Phalcon\Acl\Resource,
	Phalcon\Acl\Exception;

/**
 * Class AbstractService
 *
 * @category   Engine
 * @package    Acl
 * @subpackage Adapter
 */
class Database extends Adapter implements AdapterInterface
{
    use \Engine\Tools\Traits\DIaware;

    /**
     * Options array
     * @var array
     */
    protected $_options;

    /**
     * Database adapter
     * @var \Phalcon\Db\Adapter\Pdo
     */
    protected $_db;

	/**
	 * Engine\Acl\Adapter\Database
	 *
	 * @param array $options
     * @param \Phalcon\DiInterface $di
	 */
	public function __construct($options, \Phalcon\DiInterface $di)
	{
        $this->setDi($di);

		if (!is_array($options)) {
			throw new \Engine\Exception("Acl options must be an array");
		}

		if (!isset($options['db'])) {
			throw new \Engine\Exception("Parameter 'db' is required");
		} else {
            if (is_object($options['db'])) {
                $this->_db = $options['db'];
            } else {
                $this->_db = $this->_di->get($options['db']);
            }
        }

		if (!isset($options['roles'])) {
			throw new \Engine\Exception("Parameter 'roles' is required");
		}

		if (!isset($options['resources'])) {
			throw new \Engine\Exception("Parameter 'resources' is required");
		}

		if (!isset($options['resourcesAccesses'])) {
			throw new \Engine\Exception("Parameter 'resourcesAccesses' is required");
		}

		if (!isset($options['accessList'])) {
			throw new \Engine\Exception("Parameter 'accessList' is required");
		}

        if (!isset($options['rolesInherits'])) {
            throw new \Engine\Exception("Parameter 'rolesInherits' is required");
        }

		$this->_options = $options;
	}

	/**
	 * Adds a role to the ACL list. Second parameter lets to inherit access data from other existing role
	 *
	 * Example:
	 * <code>$acl->addRole(new Phalcon\Acl\Role('administrator'), 'consultor');</code>
	 * <code>$acl->addRole('administrator', 'consultor');</code>
	 *
	 * @param  string $role
	 * @param  array $accessInherits
	 * @return boolean
	 */
	public function addRole($role, $accessInherits=null)
	{
		if (!is_object($role)) {
			$role = new Role($role);
		}

		$exists = $this->_db->fetchOne('SELECT COUNT(*) FROM '.$this->_options['roles']." WHERE name = ?", null, [$role->getName()]);
		if (!$exists[0]) {
            $this->_db->execute('INSERT INTO '.$this->_options['roles']." VALUES (?, ?)", [$role->getName(), $role->getDescription()]);
            $this->_db->execute('INSERT INTO '.$this->_options['accessList']." VALUES (?, ?, ?, ?)", [$role->getName(), '*', '*', $this->_defaultAccess]);
		}

		if ($accessInherits) {
			return $this->addInherit($role->getName(), $accessInherits);
		}

		return true;
	}

	/**
	 * Do a role inherit from another existing role
	 *
	 * @param string $roleName
	 * @param string $roleToInherit
	 */
	public function addInherit($roleName, $roleToInherit)
	{
		$sql = 'SELECT COUNT(*) FROM '.$this->_options['roles']." WHERE name = ?";
		$exists = $this->_db->fetchOne($sql, null, [$roleToInherit]);
		if (!$exists[0]) {
			throw new \Engine\Exception("Role '".$roleToInherit."' does not exist in the role list");
		}

		$sql = 'SELECT COUNT(*) FROM '.$this->_options['rolesInherits']." WHERE roles_name = ? AND roles_inherit = ?";
		$exists = $this->_db->fetchOne($sql, null, [$roleName, $roleToInherit]);
		if (!$exists[0]) {
            $this->_db->execute('INSERT INTO '.$this->_options['rolesInherits']." VALUES (?, ?)", [$roleName, $roleToInherit]);
		}
	}

	/**
	 * Check whether role exist in the roles list
	 *
	 * @param  string $roleName
	 * @return boolean
	 */
	public function isRole($roleName)
	{
		$exists = $this->_db->fetchOne('SELECT COUNT(*) FROM '.$this->_options['roles']." WHERE name = ?", null, [$roleName]);
		return (bool) $exists[0];
	}

	/**
	 * Check whether resource exist in the resources list
	 *
	 * @param  string $resourceName
	 * @return boolean
	 */
	public function isResource($resourceName)
	{
		$exists = $this->_db->fetchOne('SELECT COUNT(*) FROM '.$this->_options['resources']." WHERE name = ?", null, [$resourceName]);
		return (bool) $exists[0];
	}

	/**
	 * Adds a resource to the ACL list
	 *
	 * Access names can be a particular action, by example
	 * search, update, delete, etc or a list of them
	 *
	 * Example:
	 * <code>
	 * //Add a resource to the the list allowing access to an action
	 * $acl->addResource(new Phalcon\Acl\Resource('customers'), 'search');
	 * $acl->addResource('customers', 'search');
	 *
	 * //Add a resource  with an access list
	 * $acl->addResource(new Phalcon\Acl\Resource('customers'), ['create', 'search'));
	 * $acl->addResource('customers', ['create', 'search'));
	 * </code>
	 *
	 *
	 * @param   \Phalcon\Acl\Resource $resource
	 * @return  boolean
	 */
	public function addResource($resource, $accessList=null)
	{
		if (!is_object($resource)) {
			$resource = new Resource($resource);
		}

		$exists = $this->_db->fetchOne('SELECT COUNT(*) FROM '.$this->_options['resources']." WHERE name = ?", null, [$resource->getName()]);
		if (!$exists[0]) {
            $this->_db->execute('INSERT INTO '.$this->_options['resources']." VALUES (?, ?)", [$resource->getName(), $resource->getDescription()]);
		}

		if ($accessList) {
			return $this->addResourceAccess($resource->getName(), $accessList);
		}

		return true;
	}

	/**
	 * Adds access to resources
	 *
	 * @param string $resourceName
	 * @param mixed $accessList
	 */
	public function addResourceAccess($resourceName, $accessList)
	{

		if (!$this->isResource($resourceName)) {
			throw new \Engine\Exception("Resource '".$resourceName."' does not exist in ACL");
		}
		$sql = 'SELECT COUNT(*) FROM '.$this->_options['resourcesAccesses']." WHERE resources_name = ? AND access_name = ?";
		if (is_array($accessList)) {
			foreach ($accessList as $accessName) {
				$exists = $this->_db->fetchOne($sql, null, [$resourceName, $accessName]);
				if (!$exists[0]) {
                    $this->_db->execute('INSERT INTO '.$this->_options['resourcesAccesses']." VALUES (?, ?)", [$resourceName, $accessName]);
				}
			}
		} else {
			$exists = $this->_db->fetchOne($sql, null, [$resourceName, $accessList]);
			if (!$exists[0]) {
                $this->_db->execute('INSERT INTO '.$this->_options['resourcesAccesses']." VALUES (?, ?)", [$resourceName, $accessList]);
			}
		}

		return true;
	}

	/**
	 * Returns all resources in the access list
	 *
	 * @return \Phalcon\Acl\Resource[]
	 */
	public function getResources()
	{
		$resources = [];
		$sql = 'SELECT * FROM '.$this->_options['resources'];
		foreach ($this->_db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC) as $row) {
			$resources[] = new Resource($row['name'], $row['description']);
		}

		return $resources;
	}

	/**
	 * Returns all resources in the access list
	 *
	 * @return \Phalcon\Acl\Role[]
	 */
	public function getRoles()
	{
		$roles = [];
		$sql = 'SELECT * FROM '.$this->_options['roles'];
		foreach ($this->_db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC) as $row) {
			$roles[] = new Role($row['name'], $row['description']);
		}

		return $roles;
	}

	/**
	 * Removes an access from a resource
	 *
	 * @param string $resourceName
	 * @param mixed $accessList
	 */
	public function dropResourceAccess($resourceName, $accessList)
	{

	}

	/**
	 * Inserts/Updates a permission in the access list
	 *
	 * @param string $roleName
	 * @param string $resourceName
	 * @param string $access
	 * @param int $access
	 * @return boolean
	 */
	protected function _insertOrUpdateAccess($roleName, $resourceName, $accessName, $action)
	{
		/**
		 * Check if the access is valid in the resource
		 */
		$sql = 'SELECT COUNT(*) FROM '.$this->_options['resourcesAccesses']." WHERE resources_name = ? AND access_name = ?";
		$exists = $this->_db->fetchOne($sql, null, [$resourceName, $accessName]);
		if (!$exists[0]) {
			throw new \Engine\Exception("Access '".$accessName."' does not exist in resource '".$resourceName."' in ACL");
		}

		/**
		 * Update the access in access_list
		 */
		$sql = 'SELECT COUNT(*) FROM '.$this->_options['accessList']." WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
		$exists = $this->_db->fetchOne($sql, null, [$roleName, $resourceName, $accessName]);
		if (!$exists[0]) {
			$sql = 'INSERT INTO '.$this->_options['accessList'].' VALUES (?, ?, ?, ?)';
			$params = [$roleName, $resourceName, $accessName, $action];
		} else {
			$sql = 'UPDATE '.$this->_options['accessList'].' SET allowed = ? WHERE roles_name = ? AND resources_name = ? AND access_name = ?';
			$params = [$action, $roleName, $resourceName, $accessName];
		}

        $this->_db->execute($sql, $params);

		/**
		 * Update the access '*' in access_list
		 */
		$sql = 'SELECT COUNT(*) FROM '.$this->_options['accessList']." WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
		$exists = $this->_db->fetchOne($sql, null, [$roleName, $resourceName, '*']);
		if (!$exists[0]) {
			$sql = 'INSERT INTO '.$this->_options['accessList'].' VALUES (?, ?, ?, ?)';
            $this->_db->execute($sql, [$roleName, $resourceName, '*', $this->_defaultAccess]);
		}

		return true;
	}

	/**
	 * Inserts/Updates a permission in the access list
	 *
	 * @param string $roleName
	 * @param string $resourceName
	 * @param string $access
	 * @param int $access
	 * @return boolean
	 */
	protected function _allowOrDeny($roleName, $resourceName, $access, $action)
	{
		if (!$this->isRole($roleName)) {
			throw new \Engine\Exception('Role "'.$roleName.'" does not exist in the list');
		}

		if (is_array($access)) {
			foreach ($access as $accessName) {
				$this->_insertOrUpdateAccess($roleName, $resourceName, $accessName, $action);
			}
		} else {
			$this->_insertOrUpdateAccess($roleName, $resourceName, $access, $action);
		}

        return true;
	}

	/**
	 * Allow access to a role on a resource
	 *
	 * You can use '*' as wildcard
	 *
	 * Ej:
	 * <code>
	 * //Allow access to guests to search on customers
	 * $acl->allow('guests', 'customers', 'search');
	 *
	 * //Allow access to guests to search or create on customers
	 * $acl->allow('guests', 'customers', ['search', 'create'));
	 *
	 * //Allow access to any role to browse on products
	 * $acl->allow('*', 'products', 'browse');
	 *
	 * //Allow access to any role to browse on any resource
	 * $acl->allow('*', '*', 'browse');
	 * </code>
	 *
	 * @param string $roleName
	 * @param string $resourceName
	 * @param mixed $access
	 */
	public function allow($roleName, $resourceName, $access)
	{
		return $this->_allowOrDeny($roleName, $resourceName, $access, Acl::ALLOW);
	}

	/**
	 * Deny access to a role on a resource
	 *
	 * You can use '*' as wildcard
	 *
	 * Ej:
	 * <code>
	 * //Deny access to guests to search on customers
	 * $acl->deny('guests', 'customers', 'search');
	 *
	 * //Deny access to guests to search or create on customers
	 * $acl->deny('guests', 'customers', ['search', 'create'));
	 *
	 * //Deny access to any role to browse on products
	 * $acl->deny('*', 'products', 'browse');
	 *
	 * //Deny access to any role to browse on any resource
	 * $acl->deny('*', '*', 'browse');
	 * </code>
	 *
	 * @param string $roleName
	 * @param string $resourceName
	 * @param mixed $access
	 * @return boolean
	 */
	public function deny($roleName, $resourceName, $access)
	{
		return $this->_allowOrDeny($roleName, $resourceName, $access, Acl::DENY);
	}

	/**
	 * Check whether a role is allowed to access an action from a resource
	 *
	 * <code>
	 * //Does Andres have access to the customers resource to create?
	 * $acl->isAllowed('Andres', 'Products', 'create');
	 *
	 * //Do guests have access to any resource to edit?
	 * $acl->isAllowed('guests', '*', 'edit');
	 * </code>
	 *
	 * @param  string $role
	 * @param  string $resource
	 * @param  mixed $accessList
	 * @return boolean
	 */
	public function isAllowed($role, $resource, $access)
	{
		/**
		 * Check if there is a specific rule for that resource/access
		 */
		$sql = 'SELECT allowed FROM '.$this->_options['accessList']." WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
		$allowed = $this->_db->fetchOne($sql, \Phalcon\Db::FETCH_NUM, [$role, $resource, $access]);
		if (is_array($allowed)) {
			return (int) $allowed[0];
		}

		/**
		 * Check if there is an common rule for that resource
		 */
		$sql = 'SELECT allowed FROM '.$this->_options['accessList']." WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
		$allowed = $this->_db->fetchOne($sql, \Phalcon\Db::FETCH_NUM, [$role, $resource, '*']);
		if (is_array($allowed)) {
			return (int) $allowed[0];
		}

		$sql = 'SELECT roles_inherit FROM '.$this->_options['rolesInherits'].' WHERE roles_name = ?';
		$inheritedRoles = $this->_db->fetchAll($sql, \Phalcon\Db::FETCH_NUM, [$role]);

		/**
		 * Check inherited roles for a specific rule
		 */
		foreach ($inheritedRoles as $row) {
			$sql = 'SELECT allowed FROM '.$this->_options['accessList']." WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
			$allowed = $this->_db->fetchOne($sql, \Phalcon\Db::FETCH_NUM, [$row[0], $resource, $access]);
			if (is_array($allowed)) {
				return (int) $allowed[0];
			}
		}

		/**
		 * Check inherited roles for a specific rule
		 */
		foreach ($inheritedRoles as $row) {
			$sql = 'SELECT allowed FROM '.$this->_options['accessList']." WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
			$allowed = $this->_db->fetchOne($sql, \Phalcon\Db::FETCH_NUM, [$row[0], $resource, '*']);
			if (is_array($allowed)) {
				return (int) $allowed[0];
			}
		}

		/**
		 * Check if there is a common rule for that access
		 */
		$sql = 'SELECT allowed FROM '.$this->_options['accessList']." WHERE roles_name = ? AND resources_name = ? AND access_name = ?";
		$allowed = $this->_db->fetchOne($sql, \Phalcon\Db::FETCH_NUM, [$role, '*', $access]);
		if (is_array($allowed)) {
			return (int) $allowed[0];
		}

		/**
		 * Return the default access action
		 */
		return $this->_defaultAccess;
	}

}
