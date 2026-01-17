<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_note(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/notes', [
                'title' => 'My first note',
                'body' => 'Some content',
            ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'My first note',
                    'body' => 'Some content',
                    'archived' => false,
                ],
            ]);

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'title' => 'My first note',
            'body' => 'Some content',
            'archived' => false,
        ]);
    }

    public function test_user_cannot_create_note_without_title(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/notes', [
                'body' => 'Missing title',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertDatabaseCount('notes', 0);
    }

    public function test_user_cannot_exceed_note_limit(): void
    {
        $user = User::factory()->create();

        Note::factory()
            ->count(Note::MAX_PER_USER)
            ->for($user)
            ->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/notes', [
                'title' => 'Too many',
            ]);

        $response
            ->assertForbidden()
            ->assertStatus(403);

        $this->assertDatabaseCount('notes', Note::MAX_PER_USER);
    }
}
