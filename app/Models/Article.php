<?php

namespace App\Models;

use App\Traits\FullTextSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, FullTextSearch;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'status',
        'type',
    ];

    protected $fullTextColumns = ['title'];

    const TYPE_ARTICLE = 1;
    const TYPE_TUTORIAL = 2;
    const TYPE_POLICY = 3;

    const STATUS_DRAFT = 1;
    const STATUS_PUBLIC = 2;
    const STATUS_LOCK = 3;

    const MEDIA_ARTICLE = 'article';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isTypeArticle(): bool
    {
        return $this->type == self::TYPE_ARTICLE;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            $article->slug = Str::slug($article->title);
        });

        static::updating(function ($article) {
            if ($article->isDirty('title')) {
                $article->slug = Str::slug($article->title);
            }
        });
    }
}
