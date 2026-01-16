<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->optional()->paragraph(),
            'archived' => false,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'archived' => true,
        ]);
    }
}
