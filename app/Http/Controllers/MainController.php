<?php

namespace App\Http\Controllers;

use App\Jobs\DuplicateImage;
use App\Models\Image;

class MainController extends Controller
{
    public function upload()
    {
        $image = Image::findOrFail(1);
        $this->dispatch(new DuplicateImage($image));
        sleep(1);
    }

    public function view()
    {

    }
}