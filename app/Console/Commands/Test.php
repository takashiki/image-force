<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Uploaders\NiupicUploader;
use App\Uploaders\SinaUploader;
use App\Uploaders\SmmsUploader;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $img = '/root/img1.jpg';
        //var_dump((new SmmsUploader())->upload('/root/img1.jpg'));
        //var_dump((new SinaUploader())->upload('/root/img1.jpg'));
        //var_dump((new NiupicUploader())->upload('/root/img1.jpg'));
        $image = Image::find(4);
        $image->check();
    }
}
