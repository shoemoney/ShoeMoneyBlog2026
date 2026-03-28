<?php

namespace App\Livewire\Admin\Posts;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.layouts.app')]
class PostIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    public array $selected = [];
    public bool $selectAll = false;
    public bool $showBulkDeleteModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = $this->getCurrentPageIds();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected(): void
    {
        $this->selectAll = count($this->selected) === count($this->getCurrentPageIds());
    }

    public function confirmBulkDelete(): void
    {
        $this->showBulkDeleteModal = true;
    }

    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function bulkDelete(): void
    {
        $ids = array_map('intval', $this->selected);
        $count = count($ids);

        Post::whereIn('id', $ids)->each(function ($post) {
            $post->categories()->detach();
            $post->tags()->detach();
            $post->delete();
        });

        $this->selected = [];
        $this->selectAll = false;
        $this->showBulkDeleteModal = false;

        session()->flash('success', "{$count} post(s) deleted successfully.");
    }

    public function delete(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->categories()->detach();
        $post->tags()->detach();
        $post->delete();

        $this->selected = array_values(array_diff($this->selected, [(string) $id]));

        session()->flash('success', 'Post deleted successfully.');
    }

    protected function getCurrentPageIds(): array
    {
        return Post::posts()
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->status === 'published', fn ($q) => $q->published())
            ->when($this->status === 'draft', fn ($q) => $q->draft())
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function render()
    {
        $posts = Post::posts()
            ->with('author')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->status === 'published', function ($query) {
                $query->published();
            })
            ->when($this->status === 'draft', function ($query) {
                $query->draft();
            })
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.posts.post-index', [
            'posts' => $posts,
        ]);
    }
}
