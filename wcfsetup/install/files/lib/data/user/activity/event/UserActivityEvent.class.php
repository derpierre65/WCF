<?php
namespace wcf\data\user\activity\event;
use wcf\data\DatabaseObject;

/**
 * Represents a user's activity.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 */
class UserActivityEvent extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_activity_event';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'eventID';
	
	/**
	 * @see	wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		// treat additional data as data variables if it is an array
		if ($value === null && isset($this->data['additionalData'][$name])) {
			$value = $this->data['additionalData'][$name];
		}
		
		return $value;
	}
	
	/**
	 * @see	wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = array();
		}
	}
}
