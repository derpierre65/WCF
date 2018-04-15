<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Abstract implementation of a form field for numeric values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
abstract class AbstractNumericFormField extends AbstractFormField implements IMaximumFormField, IMinimumFormField, INullableFormField, IPlaceholderFormField, ISuffixedFormField {
	use TMaximumFormField;
	use TMinimumFormField;
	use TNullableFormField;
	use TPlaceholderFormField;
	use TSuffixedFormField;
	
	/**
	 * step value for the input element
	 * @var	null|number
	 */
	protected $__step;
	
	/**
	 * is `true` if only integer values are supported
	 * @var	bool
	 */
	protected $integerValues = false;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__numericFormField';
	
	/**
	 * Returns the default value for the input element's step attribute.
	 * 
	 * @return	number|string
	 */
	protected function getDefaultStep() {
		if ($this->integerValues) {
			return 1;
		}
		else {
			return 'any';
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		if ($this->getValue() === null && !$this->isNullable()) {
			if ($this->integerValues) {
				return 0;
			}
			else {
				return 0.0;
			}
		}
		
		return parent::getSaveValue();
	}
	
	/**
	 * Returns the value for the input element's step attribute. This method
	 * can either return a number or `any` if no specific step is defined.
	 * 
	 * If no step value has been set, the return value of `getDefaultStep()`
	 * is set and returned.
	 * 
	 * @return	number|string
	 */
	public function getStep() {
		if ($this->__step === null) {
			$this->__step = $this->getDefaultStep();
		}
		
		return $this->__step;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue(): IFormField {
		if (isset($_POST[$this->getPrefixedId()]) && $_POST[$this->getPrefixedId()] !== '') {
			if ($this->integerValues) {
				$this->__value = intval($_POST[$this->getPrefixedId()]);
			}
			else {
				$this->__value = floatval($_POST[$this->getPrefixedId()]);
			}
		}
		
		return $this;
	}
	
	/**
	 * Sets the value for the input element's step attribute.
	 * 
	 * @param	null|number|string	$step
	 * @return	static
	 * 
	 * @throws	\InvalidArgumentException	if the given step value is invalid
	 */
	public function step($step = null): AbstractNumericFormField {
		if ($step !== null) {
			if ($this->integerValues) {
				if (!is_int($step)) {
					throw new \InvalidArgumentException("Given step is no int, '" . gettype($step) . "' given.");
				}
			}
			else {
				if (is_string($step) && $step !== 'any') {
					throw new \InvalidArgumentException("The only valid string step value is 'any', '" . $step . "' given.");
				}
				else if (!is_numeric($step)) {
					throw new \InvalidArgumentException("Given step is no number, '" . gettype($step) . "' given.");
				}
			}
		}
		
		$this->__step = $step;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->getValue() === null) {
			if ($this->isRequired()) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
		}
		else {
			if ($this->getMinimum() !== null && $this->getValue() < $this->getMinimum()) {
				$this->addValidationError(new FormFieldValidationError('minimum', 'wcf.global.form.error.minimum', [
					'minimum' => $this->getMinimum()
				]));
			}
			else if ($this->getMaximum() !== null && $this->getValue() > $this->getMaximum()) {
				$this->addValidationError(new FormFieldValidationError('maximum', 'wcf.global.form.error.maximum', [
					'maximum' => $this->getMaximum()
				]));
			}
		}
		
		parent::validate();
	}
	
	/**
	 * @inheritDoc
	 * @return	static		this field
	 */
	public function value($value): IFormField {
		if ($value !== null) {
			if ($this->integerValues && !is_int($value)) {
				throw new \InvalidArgumentException("Given value is neither `null` nor an int, " . gettype($value) . " given.");
			}
			else if (!$this->integerValues && !is_numeric($value)) {
				throw new \InvalidArgumentException("Given value is neither `null` nor a number, " . gettype($value) . " given.");
			}
		}
		
		return parent::value($value);
	}
}