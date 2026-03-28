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
    public string $tagSearch = '';
    public array $tagResults = [];
    public array $selectedTagNames = [];

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
        $this->selectedTagNames = $post->tags->pluck('name', 'id')->toArray();
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

    public function updatedTagSearch(): void
    {
        if (strlen($this->tagSearch) < 2) {
            $this->tagResults = [];
            return;
        }

        $this->tagResults = Tag::where('name', 'like', '%' . $this->tagSearch . '%')
            ->whereNotIn('id', $this->selectedTags)
            ->orderBy('name')
            ->limit(15)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function addTag(int $id): void
    {
        if (!in_array($id, $this->selectedTags)) {
            $tag = Tag::find($id);
            if ($tag) {
                $this->selectedTags[] = $id;
                $this->selectedTagNames[$id] = $tag->name;
            }
        }
        $this->tagSearch = '';
        $this->tagResults = [];
    }

    public function removeTag(int $id): void
    {
        $this->selectedTags = array_values(array_diff($this->selectedTags, [$id]));
        unset($this->selectedTagNames[$id]);
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

    public bool $showDeleteModal = false;

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function delete(): void
    {
        $this->post->categories()->detach();
        $this->post->tags()->detach();
        $this->post->delete();

        session()->flash('success', 'Post deleted successfully.');
        $this->redirect(route('admin.posts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.posts.post-edit', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
