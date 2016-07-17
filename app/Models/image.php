<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'image';

    public function duplicate()
    {
file_put_contents('/root/test/test', '');
    }
}
