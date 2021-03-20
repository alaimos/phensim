<?php

namespace App\Rules;

use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Validation\Rule;
use Livewire\TemporaryUploadedFile;

class InputFileRule implements Rule
{

    private bool $isRequired;

    private $validationFunction;

    /**
     * Create a new rule instance.
     *
     * @param  bool  $isRequired
     * @param  callable|null  $validationFunction
     */
    public function __construct(bool $isRequired = false, callable $validationFunction = null)
    {
        $this->isRequired = $isRequired;
        $this->validationFunction = $validationFunction;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (
            ($value instanceof TemporaryUploadedFile && $value->exists()) ||
            ($value instanceof UploadedFile && $value->isFile())
        ) {
            if ($value->getMimeType() !== 'text/plain') {
                return false;
            }
            if ($this->validationFunction !== null && is_callable($this->validationFunction)) {
                return call_user_func($this->validationFunction, $value->getRealPath());
            }

            return true;
        }

        return !$this->isRequired;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'An invalid file has been provided.';
    }
}
