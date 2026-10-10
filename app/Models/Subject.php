<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'credits',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
        ];
    }

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }
}
