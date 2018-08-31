<?php

namespace MatrixLab\LaravelAdvancedSearch;

use Closure;

class When
{
    private $when;
    private $success;
    private $fail;

    public function __construct($value, ...$args)
    {
        if ($value instanceof Closure) {
            $this->when = boolval($value(...$args));
        } else {
            $this->when = boolval($value);
        }
    }

    public static function make(...$args)
    {
        return new static(...$args);
    }

    /**
     * 成功
     *
     * @param $value
     * @return $this
     */
    public function success($value)
    {
        $this->success = $value;
        return $this;
    }

    /**
     * 失败
     *
     * @param $value
     * @return $this
     */
    public function fail($value)
    {
        $this->fail = $value;
        return $this;
    }

    /**
     * 输出结果
     *
     * @return null
     */
    public function result()
    {
        if ($this->when === true) {
            return $this->success;
        }
        if ($this->when === false) {
            return $this->fail;
        }

        return null;
    }
}
