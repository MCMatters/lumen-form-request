<?php

declare(strict_types = 1);

namespace McMatters\LumenFormRequest;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use const null;
use function count, is_array, method_exists;

/**
 * Class FormRequest
 *
 * @package McMatters\LumenFormRequest
 */
class FormRequest extends Request
{
    /**
     * @return void
     * @throws \Illuminate\Validation\UnauthorizedException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate()
    {
        $validator = $this->getValidator();

        if (!$this->authorize()) {
            throw new UnauthorizedException('You are not authorized to perform this request.');
        }

        if (!$validator->passes()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function allFiltered(): array
    {
        $filtered = [];

        foreach ($this->all() as $key => $item) {
            if (null !== $item) {
                $filtered[$key] = $item;
            }
        }

        return $filtered;
    }

    /**
     * @return mixed
     */
    protected function getRequestIdentifier()
    {
        $route = $this->route();

        if (is_array($route) && count($route) === 3) {
            if (method_exists($this, 'getIdentifier')) {
                return $route[2][$this->getIdentifier()] ?? null;
            }

            return $route[2]['id'] ?? null;
        }

        return null;
    }

    /**
     * @return \Illuminate\Validation\Validator
     */
    protected function getValidator(): Validator
    {
        return Container::getInstance()->make('validator')->make(
            $this->all(),
            Container::getInstance()->call([$this, 'rules']),
            $this->messages(),
            $this->attributes()
        );
    }
}
