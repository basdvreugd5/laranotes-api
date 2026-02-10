<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    public const MAX_PER_USER = 100;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'body',
        'archived',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'archived' => 'boolean',
    ];

    /**
     * Get the user that owns the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include notes for a given user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to only include active (not archived) notes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('archived', false);
    }

    /**
     * Scope a query to only include archived notes.
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('archived', true);
    }

    /**
     * Scope a query to search notes by title or body.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('body', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter notes by archived state.
     */
    public function scopeArchivedState(Builder $query, ?bool $archived): Builder
    {
        if ($archived === null) {
            return $query;
        }

        return $query->where('archived', $archived);
    }

    /**
     * Archive the note.
     */
    public function archive(): void
    {
        if ($this->archived) {
            return;
        }

        $this->archived = true;
        $this->save();
    }
}
