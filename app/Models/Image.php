<?php

namespace App\Models;

use App\Jobs\CheckImage;
use App\Jobs\DuplicateImage;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Image.
 *
 * @property int $id
 * @property string $sha1
 * @property int $copy_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Image whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Image whereSha1($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Image whereCopyCount($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Image whereUpdatedAt($value)
 */
class Image extends \Eloquent
{
    protected $table = 'image';

    protected $fillable = [
        'sha1',
    ];

    protected $attributes = [
        'copy_count' => 0,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function copies()
    {
        return $this->hasMany(\App\Models\ImageCopies::class);
    }

    public function getAvailableCopies()
    {
        return $this->copies()->where('status', ImageCopies::AVAILABLE)->get();
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (Image $image) {
            dispatch(new DuplicateImage($image));
        });
    }

    public static function getModel($file)
    {
        $sha1 = sha1_file($file);
        $image = static::where(['sha1' => $sha1])->first();
        if (!$image) {
            $image = static::create(['sha1' => $sha1]);
            ImageCopies::storage($image->id, $file);
        }

        return $image;
    }

    public function check()
    {
        foreach ($this->getAvailableCopies() as $copy) {
            $this->copy_count -= $copy->getAvailability() !== ImageCopies::AVAILABLE ? 1 : 0;
        }

        if ($this->copy_count < 2) {
            $this->duplicate();
        }
    }

    public function duplicate()
    {
        $copies = $this->getAvailableCopies();
        if (empty($copies)) {
            throw new \Exception('Boom!');
        }

        foreach (ImageStorage::getUploaders() as $id => $uploader) {
            ImageCopies::storage($this->id, $copies[0]->getUrl, $id);
        }
    }

    public function getUrl()
    {
        return \URL::to($this->sha1);
    }

    public function getRealUrl($scheme = 'relative')
    {
        dispatch(new CheckImage($this));
        $copy = $this->copies()->where('status', 1)->firstOrFail();
        ++$copy->access_count;
        $copy->save();

        return $copy->getUrl($scheme);
    }
}
