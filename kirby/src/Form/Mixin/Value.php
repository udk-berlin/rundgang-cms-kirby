<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Value
{
	protected mixed $default = null;
	protected mixed $value = null;

	/**
	 * @deprecated 5.0.0 Use `::toStoredValue()` instead
	 */
	public function data(bool $default = false): mixed
	{
		return $this->toStoredValue($default);
	}

	/**
	 * Returns the default value of the field
	 */
	public function default(): mixed
	{
		if (is_string($this->default) === false) {
			return $this->default;
		}

		return $this->model->toString($this->default);
	}

	/**
	 * Sets a new value for the field
	 */
	public function fill(mixed $value): static
	{
		$this->value = $value;
		$this->errors = null;

		return $this;
	}

	/**
	 * Checks if the field has a value
	 */
	public function hasValue(): bool
	{
		return true;
	}

	/**
	 * Checks if the field is empty
	 */
	public function isEmpty(): bool
	{
		return $this->isEmptyValue($this->toFormValue());
	}

	/**
	 * Checks if the given value is considered empty
	 */
	public function isEmptyValue(mixed $value = null): bool
	{
		return in_array($value, [null, '', []], true);
	}

	/**
	 * A field might have a value, but can still not be submitted
	 * because it is disabled, not translatable into the given
	 * language or not active due to a `when` rule.
	 */
	public function isSubmittable(Language $language): bool
	{
		if ($this->hasValue() === false) {
			return false;
		}

		if ($this->isDisabled() === true) {
			return false;
		}

		if ($this->isTranslatable($language) === false) {
			return false;
		}

		if ($this->isActive() === false) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the field needs a value before being saved;
	 * this is the case if all of the following requirements are met:
	 * - The field has a value
	 * - The field is required
	 * - The field is currently empty
	 * - The field is not currently inactive because of a `when` rule
	 */
	protected function needsValue(): bool
	{
		if (
			$this->hasValue() === false ||
			$this->isRequired() === false ||
			$this->isEmpty() === false ||
			$this->isActive() === false
		) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the field is saveable
	 * @deprecated 5.0.0 Use `::hasValue()` instead
	 */
	public function save(): bool
	{
		return $this->hasValue();
	}

	/**
	 * @internal
	 */
	protected function setDefault(mixed $default = null): void
	{
		$this->default = $default;
	}

	/**
	 * Returns the value of the field in a format to be used in forms
	 * (e.g. used as data for Panel Vue components)
	 */
	public function toFormValue(bool $default = false): mixed
	{
		if ($this->hasValue() === false) {
			return null;
		}

		if ($default === true && $this->isEmpty() === true) {
			return $this->default();
		}

		return $this->value;
	}

	/**
	 * Returns the value of the field in a format
	 * to be stored by our storage classes
	 */
	public function toStoredValue(bool $default = false): mixed
	{
		return $this->toFormValue($default);
	}

	/**
	 * Returns the value of the field if it has a value
	 * otherwise it returns null
	 *
	 * @see `self::toFormValue()`
	 * @todo might get deprecated or reused later
	 */
	public function value(bool $default = false): mixed
	{
		return $this->toFormValue($default);
	}
}
