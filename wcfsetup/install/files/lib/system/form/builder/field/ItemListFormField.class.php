<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;
use wcf\util\ArrayUtil;

/**
 * Implementation of a form field that allows entering a list of items.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class ItemListFormField extends AbstractFormField {
	/**
	 * type of the returned save value (see `SAVE_VALUE_TYPE_*` constants)
	 * @var	string
	 */
	protected $saveValueType;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__itemListFormField';
	
	/**
	 * save value return type so that an array with the item values will be returned
	 * @var	string
	 */
	const SAVE_VALUE_TYPE_ARRAY = 'array';
	
	/**
	 * save value return type so that comma-separated list with the item values
	 * will be returned
	 * @var	string
	 */
	const SAVE_VALUE_TYPE_CSV = 'csv';
	
	/**
	 * save value return type so that space-separated list with the item values
	 * will be returned
	 * @var	string
	 */
	const SAVE_VALUE_TYPE_SSV = 'ssv';
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		switch ($this->getSaveValueType()) {
			case self::SAVE_VALUE_TYPE_ARRAY:
				return '';
			
			case self::SAVE_VALUE_TYPE_CSV:
				return implode(',', $this->getValue() ?: []);
			
			case self::SAVE_VALUE_TYPE_SSV:
				return implode(' ', $this->getValue() ?: []);
			
			default:
				throw new \LogicException("Unreachable");
		}
	}
	
	/**
	 * Returns the type of the returned save value (see `SAVE_VALUE_TYPE_*` constants).
	 * 
	 * If no save value type has been set, `SAVE_VALUE_TYPE_CSV` will be set and returned.
	 * 
	 * @return	string
	 */
	public function getSaveValueType(): string {
		if ($this->saveValueType === null) {
			$this->saveValueType = self::SAVE_VALUE_TYPE_CSV;
		}
		
		return $this->saveValueType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue(): bool {
		// arrays cannot be returned as a simple save value
		return $this->getSaveValueType() !== self::SAVE_VALUE_TYPE_ARRAY;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate(): IFormNode {
		parent::populate();
		
		// an array should be passed as a parameter outside of the `data` array
		if ($this->getSaveValueType() === self::SAVE_VALUE_TYPE_ARRAY) {
			$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('itemList', function(IFormDocument $document, array $parameters) {
				if (is_array($this->getValue())) {
					$parameters[$this->getId()] = $this->getValue();
				}
				
				return $parameters;
			}));
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue(): IFormField {
		if (isset($_POST[$this->getPrefixedId()]) && is_array($_POST[$this->getPrefixedId()])) {
			$this->__value = array_unique(ArrayUtil::trim($_POST[$this->getPrefixedId()]));
		}
		
		return $this;
	}
	
	/**
	 * Sets the type of the returned save value (see `SAVE_VALUE_TYPE_*` constants).
	 * 
	 * @param	string			$saveValueType	type of the returned save value
	 * @return	ItemListFormField			this field
	 * @throws	\BadMethodCallException			if save value type has already been set
	 * @throws	\InvalidArgumentException		if given save value type is invalid
	 */
	public function saveValueType(string $saveValueType): ItemListFormField {
		if ($this->saveValueType !== null) {
			throw new \BadMethodCallException("Save value type has already been set.");
		}
		
		if ($saveValueType !== self::SAVE_VALUE_TYPE_ARRAY && $saveValueType !== self::SAVE_VALUE_TYPE_CSV && $saveValueType !== self::SAVE_VALUE_TYPE_SSV) {
			throw new \InvalidArgumentException("Unknown save value type '{$saveValueType}'.");
		}
		
		$this->saveValueType = $saveValueType;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value): IFormField {
		switch ($this->getSaveValueType()) {
			case self::SAVE_VALUE_TYPE_ARRAY:
				if (is_array($value)) {
					$this->__value = $value;
				}
				else {
					throw new \InvalidArgumentException("Given value is no array, '" . gettype($value) . "' given.");
				}
				
				break;
			
			case self::SAVE_VALUE_TYPE_CSV:
				if (is_string($value)) {
					$this->__value = explode(',', $value);
				}
				else {
					throw new \InvalidArgumentException("Given value is no string, '" . gettype($value) . "' given.");
				}
				
				break;
			
			case self::SAVE_VALUE_TYPE_SSV:
				if (is_string($value)) {
					$this->__value = explode(' ', $value);
				}
				else {
					throw new \InvalidArgumentException("Given value is no string, '" . gettype($value) . "' given.");
				}
				
				break;
				
			default:
				throw new \LogicException("Unreachable");
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if (is_array($this->getValue())) {
			$invalidItems = [];
			foreach ($this->getValue() as $item) {
				switch ($this->getSaveValueType()) {
					case self::SAVE_VALUE_TYPE_CSV:
						if (strpos($item, ',') !== false) {
							$invalidItems[] = $item;
						}
						
						break;
					
					case self::SAVE_VALUE_TYPE_SSV:
						if (strpos($item, ' ') !== false) {
							$invalidItems[] = $item;
						}
						
						break;
					
					default:
						throw new \LogicException("Unreachable");
				}
			}
			
			if (!empty($invalidItems)) {
				$this->addValidationError(new FormFieldValidationError(
					'separator',
					'wcf.form.field.itemList.error.separator',
					[
						'invalidItems' => $invalidItems,
						'separator' => $this->getSaveValueType() === self::SAVE_VALUE_TYPE_CSV ? ',' : ' '
					]
				));
			}
		}
		
		parent::validate();
	}
}