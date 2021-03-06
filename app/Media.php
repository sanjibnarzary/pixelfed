<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class Media extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function url()
    {
        if(!empty($this->remote_media) && $this->remote_url) {
            $url = $this->remote_url;
        } else {
            $path = $this->media_path;
            $url = Storage::url($path);
        }

        return url($url);
    }

    public function thumbnailUrl()
    {
        $path = $this->thumbnail_path;
        $url = Storage::url($path);

        return url($url);
    }

    public function mimeType()
    {
        return explode('/', $this->mime)[0];
    }

    public function activityVerb()
    {
        $verb = 'Image';
        switch ($this->mimeType()) {
            case 'image':
                break;

            case 'video':
                $verb = 'Video';
                break;
            
            default:
                $verb = 'Document';
                break;
        }
        return $verb;
    }

    public function getMetadata()
    {
        return json_decode($this->metadata, true, 3);
    }

    public function getModel()
    {
        if(empty($this->metadata)) {
            return false;
        }
        $meta = $this->getMetadata();
        if($meta && isset($meta['Model'])) {
            return $meta['Model'];
        }
    }
}
