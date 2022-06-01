<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int id
 * @method static static create(array $attributes)
 */
class BaseModel extends Model
{
    /**
     * Encrypt
     *
     * @param string $property
     * @return string
     */
    public function encrypt(string $property) : string
    {
        return base64_encode($this->$property);
    }

    /**
     * Decrypt
     *
     * @param string $value
     * @return string
     */
    public static function decrypt(string $value) : string
    {
        return base64_decode($value);
    }
}
