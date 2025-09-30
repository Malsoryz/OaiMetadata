<?php

namespace Leconfe\OaiMetadata\Classes;

use Exception;
use InvalidArgumentException;
use TypeError;
use Illuminate\Support\Collection;

class ExceptionCollection extends Exception
{
    protected Collection $errors;
    protected string $class;

    public function __construct(string $class, Collection|array $errors = [])
    {
        parent::__construct("This kind of exceptions must be handled by using 'try catch'");

        $this->errors = collect();

        if (class_exists($class)) {
            $this->class = $class;
        } else throw new InvalidArgumentException("class {$class} does not exists.");

        $tmpErrors = $errors instanceof Collection ? $errors : collect($errors);
        foreach ($tmpErrors as $item) {
            if (! $item instanceof ($this->class)) {
                throw new InvalidArgumentException("Items is not type of defined class.");
            }
        }

        $this->errors = $errors instanceof Collection ? $errors : collect($errors);
    }

    public function throw(mixed $error): static
    {
        if ($error instanceof ($this->class)) {
            $this->errors->push($error);
        } else {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            $type = is_object($error) ? get_class($error) : gettype($error);
            throw new TypeError(__METHOD__."(): Argument #1 (\$error) must be type {$this->class}, {$type} given, called in {$trace['file']} on line {$trace['line']}");
        }

        return $this;
    }

    public function hasExceptions(): bool
    {
        return $this->errors->count() > 0;
    }

    public function getAllExceptions(): Collection
    {
        return $this->errors;
    }
}