<?php

namespace App\Models;

use App\Jobs\CheckImage;
use App\Jobs\DuplicateImage;

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

    public function firstAvailableCopy()
    {
        return $this->copies()->where('status', ImageCopies::AVAILABLE)->first();
    }

    public static function getModel($file)
    {
        $sha1 = sha1_file($file);
        $image = static::where(['sha1' => $sha1])->first();
        if (!$image) {
            $image = static::create(['sha1' => $sha1]);
        }

        if ($image->copy_count < 1) {
            if (!ImageCopies::storage($image, $file, ImageStorage::NIUPIC)) {
                return false;
            }
            dispatch(new DuplicateImage($image));
        }

        return $image;
    }

    public function check()
    {
        foreach ($this->getAvailableCopies() as $copy) {
            $this->copy_count -= $copy->getAvailability() !== ImageCopies::AVAILABLE ? 1 : 0;
        }

        if ($this->copy_count < 3) {
            $this->duplicate();
        }
    }

    public function duplicate()
    {
        $copy = $this->firstAvailableCopy();
        if (!$copy) {
            throw new \Exception('Boom!');
        }

        foreach (ImageStorage::getUploaders() as $id => $uploader) {
            if (!ImageCopies::where(['image_id' => $this->id, 'storage_id' => $id])->first()) {
                ImageCopies::storage($this, $copy->getUrl(), $id);
            }
        }
    }

    public function getUrl()
    {
        return \URL::to($this->sha1);
    }

    public function getRealUrl($scheme = 'relative')
    {
        dispatch(new CheckImage($this));
        $copy = $this->firstAvailableCopy();
        $copy->increaseAccessCount();

        return $copy->getUrl($scheme);
    }

    public function increaseCopyCount()
    {
        ++$this->copy_count;

        return $this->save();
    }
}
