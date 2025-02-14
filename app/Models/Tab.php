<?php

namespace App\Models;

use App\Traits\FullTextSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;

class Tab extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, FullTextSearch;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'author',
        'price',
        'category_id',
        'youtube_url',
        'discount_money',
    ];
    protected $fullTextColumns = ['name', 'author'];

    const MEDIA_TAB_PDF = 'tab_pdf';
    const MEDIA_TAB_IMAGE = 'tab_image';

    /**
     * Người sở hữu tab.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Một tab thuộc về một category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewTabs()
    {
        return $this->hasMany(ReviewTab::class, 'tab_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'tab_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tab) {
            $tab->slug = Str::slug($tab->name);
        });

        static::updating(function ($tab) {
            if ($tab->isDirty('name')) {
                $tab->slug = Str::slug($tab->name);
            }
        });
    }
}
