<?php

namespace App\Livewire\Admin\Posts;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
class PostCreate extends Component
{
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $excerpt = '';
    public string $status = 'draft';
    public array $selectedCategories = [];
    public array $selectedTags = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts,slug|max:255',
            'content' => 'required',
            'excerpt' => 'nullable|max:500',
            'status' => 'required|in:draft,published',
            'selectedCategories' => 'array',
            'selectedTags' => 'array',
        ];
    }

    public function updatedTitle(): void
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function save(): void
    {
        $this->validate();

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt ?: null,
            'status' => $this->status,
            'published_at' => $this->status === 'published' ? now() : null,
        ]);

        // Sync relationships
        if (!empty($this->selectedCategories)) {
            $post->categories()->sync($this->selectedCategories);
        }

        if (!empty($this->selectedTags)) {
            $post->tags()->sync($this->selectedTags);
        }

        session()->flash('success', 'Post created successfully.');

        $this->redirect(route('admin.posts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.posts.post-create', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }
}
