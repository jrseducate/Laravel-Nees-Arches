<?php

namespace App\Http\Controllers;

use App\Models\BaseModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Decrypt
     *
     * @param string $value
     * @return string
     */
    public function decrypt(string $value) : string
    {
        return BaseModel::decrypt($value);
    }

    /**
     * Decrypt Keys
     *
     * @param array $data
     * @param string[] $keys
     * @return array
     */
    public function decryptKeys(array $data, array $keys) : array
    {
        foreach($keys as $key)
            if (!empty($data[$key]))
            {
                if (!is_array($data[$key]))
                    $data[$key] = $this->decrypt($data[$key]);
                else
                    foreach($data[$key] as $key2 => $value)
                        $data[$key][$key2] = $this->decrypt($value);
            }

        return $data;
    }
}
