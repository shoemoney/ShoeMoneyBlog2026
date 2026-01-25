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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->delete();

        session()->flash('success', 'Post deleted successfully.');
    }

    public function render()
    {
        $posts = Post::query()
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
