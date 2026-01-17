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

    public function test_user_can_create_note_without_body(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/notes', [
                'title' => 'Note with no body',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('notes', [
            'title' => 'Note with no body',
            'body' => null,
        ]);
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
            ->assertForbidden();

        $this->assertDatabaseCount('notes', Note::MAX_PER_USER);
    }

    public function test_user_can_update_own_note(): void
    {
        $user = User::factory()->create();

        $note = Note::factory()
            ->for($user)
            ->create([
                'title' => 'Original title',
                'body' => 'Original body',
            ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->patchJson("/api/notes/{$note->id}", [
                'title' => 'Updated title',
                'body' => 'Updated body',
            ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated title',
                    'body' => 'Updated body',
                ],
            ]);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated title',
            'body' => 'Updated body',
        ]);
    }

    public function test_user_cannot_update_another_users_note(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $note = Note::factory()
            ->for($owner)
            ->create([
                'title' => 'Original',
            ]);

        $response = $this
            ->actingAs($other, 'sanctum')
            ->patchJson("/api/notes/{$note->id}", [
                'title' => 'Hacked title',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Original',
        ]);
    }

    public function test_user_can_archive_own_note(): void
    {
        $user = User::factory()->create();

        $note = Note::factory()
            ->for($user)
            ->create([
                'archived' => false,
            ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/notes/{$note->id}/archive");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $note->id,
                    'archived' => true,
                ],
            ]);

        $this->assertTrue($note->refresh()->archived);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'archived' => true,
        ]);
    }

    public function test_user_cannot_archive_another_users_note(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $note = Note::factory()
            ->for($owner)
            ->create([
                'archived' => false,
            ]);

        $response = $this
            ->actingAs($other, 'sanctum')
            ->postJson("/api/notes/{$note->id}/archive");

        $response->assertForbidden();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'archived' => false,
        ]);
    }

    public function test_user_can_list_their_notes(): void
    {
        $user = User::factory()->create();

        Note::factory()->count(3)->for($user)->create(['archived' => false]);

        Note::factory()->create(['archived' => false]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'body', 'archived'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_user_cannot_list_another_users_notes(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Note::factory()->count(3)->for($owner)->create(['archived' => false]);

        $response = $this
            ->actingAs($other, 'sanctum')
            ->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_guests_cannot_interact_with_notes(): void
    {
        $response = $this->getJson('/api/notes');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/notes', [
            'title' => 'Guest note',
        ]);
        $response->assertUnauthorized();
    }

    public function test_title_cannot_exceed_255_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/notes', [
                'title' => str_repeat('a', 256),
                'body' => 'Valid body',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

    }
}
