<?php

namespace Tests\Utils\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use MatrixLab\LaravelAdvancedSearch\AdvancedSearchTrait;

class Post extends Model
{
    use AdvancedSearchTrait;

    protected $guarded = [];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function parent() : BelongsTo
    {
        return $this->belongsTo(self::class);
    }

    public function children() : HasMany
    {
        return $this->hasMany(self::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
