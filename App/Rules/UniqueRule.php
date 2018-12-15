<?php

namespace App\Rules;

use Illuminate\Database\Capsule\Manager as DB;

use Rakit\Validation\Rule;

class UniqueRule extends Rule
{
    protected $message = ":attribute :value has been used";

    protected $fillableParams = ['table', 'column', 'except'];

    public function check($value): bool
    {
        // make sure required parameters exists
        $this->requireParameters(['table', 'column']);

        // getting parameters
        $column = $this->parameter('column');
        $table = $this->parameter('table');
        $except = $this->parameter('except');

        if ($except AND $except == $value) {
            return true;
        }

        $exist = DB::table($table)
            ->where($column, '=', $value)
            ->count();

        // true for valid, false for invalid
        return !$exist;
    }
}