<?php

namespace App\Livewire\Admin\Posts;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
class PostEdit extends Component
{
    public Post $post;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $excerpt = '';
    public string $status = 'draft';
    public array $selectedCategories = [];
    public array $selectedTags = [];

    public function mount(Post $post): void
    {
        $this->post = $post;
        $this->title = $post->title ?? '';
        $this->slug = $post->slug ?? '';
        $this->content = $post->content ?? '';
        $this->excerpt = $post->excerpt ?? '';
        $this->status = $post->status ?? 'draft';
        $this->selectedCategories = $post->categories->pluck('id')->toArray();
        $this->selectedTags = $post->tags->pluck('id')->toArray();
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts,slug,' . $this->post->id . '|max:255',
            'content' => 'required',
            'excerpt' => 'nullable|max:500',
            'status' => 'required|in:draft,published',
            'selectedCategories' => 'array',
            'selectedTags' => 'array',
        ];
    }

    public function updatedTitle(): void
    {
        if (empty($this->slug) || $this->slug === Str::slug($this->post->title)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function update(): void
    {
        $this->validate();

        $wasPublished = $this->post->status === 'published';
        $nowPublishing = $this->status === 'published';

        $this->post->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt ?: null,
            'status' => $this->status,
            'published_at' => $this->determinePublishedAt($wasPublished, $nowPublishing),
        ]);

        // Sync relationships
        $this->post->categories()->sync($this->selectedCategories);
        $this->post->tags()->sync($this->selectedTags);

        session()->flash('success', 'Post updated successfully.');

        $this->redirect(route('admin.posts.index'), navigate: true);
    }

    protected function determinePublishedAt(bool $wasPublished, bool $nowPublishing): ?string
    {
        // If publishing for the first time, set published_at to now
        if (!$wasPublished && $nowPublishing) {
            return now();
        }

        // If unpublishing, keep the original published_at for reference
        // If was already published and staying published, keep original date
        if ($wasPublished) {
            return $this->post->published_at;
        }

        // Draft staying as draft
        return null;
    }

    public function render()
    {
        return view('livewire.admin.posts.post-edit', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }
}
