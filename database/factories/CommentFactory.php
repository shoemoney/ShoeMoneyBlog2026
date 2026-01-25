<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wordpress_id' => fake()->unique()->randomNumber(8),
            'post_id' => Post::factory(),
            'user_id' => null,
            'parent_id' => null,
            'author_name' => fake()->name(),
            'author_email' => strtolower(fake()->safeEmail()),
            'author_url' => fake()->optional()->url(),
            'author_ip' => fake()->ipv4(),
            'content' => fake()->paragraphs(2, true),
            'status' => 'approved',
        ];
    }

    /**
     * Indicate that the comment is pending moderation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the comment is spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'spam',
        ]);
    }

    /**
     * Make the comment a reply to another comment.
     */
    public function replyTo(int $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }
}
