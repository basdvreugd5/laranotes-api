<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_note_when_under_limit(): void
    {
        $user = User::factory()->create();

        Note::factory()
            ->count(Note::MAX_PER_USER - 1)
            ->for($user)
            ->create();

        $this->assertTrue(
            $user->can('create', Note::class)
        );
    }

    public function test_user_cannot_create_note_when_at_limit(): void
    {
        $user = User::factory()->create();

        Note::factory()
            ->count(Note::MAX_PER_USER)
            ->for($user)
            ->create();

        $this->assertFalse(
            $user->can('create', Note::class)
        );
    }

    public function test_user_can_update_own_note(): void
    {
        $user = User::factory()->create();

        $note = Note::factory()
            ->for($user)
            ->create();

        $this->assertTrue(
            $user->can('update', $note)
        );
    }

    public function test_user_cannot_update_other_users_note(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $note = Note::factory()
            ->for($owner)
            ->create();

        $this->assertFalse(
            $other->can('update', $note)
        );
    }

    public function test_user_can_archive_own_note(): void
    {
        $user = User::factory()->create();

        $note = Note::factory()
            ->for($user)
            ->create();

        $this->assertTrue(
            $user->can('archive', $note)
        );
    }

    public function test_user_cannot_archive_other_users_note(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $note = Note::factory()
            ->for($owner)
            ->create();

        $this->assertFalse(
            $other->can('archive', $note)
        );
    }
}
