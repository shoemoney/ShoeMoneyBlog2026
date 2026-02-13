<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.layouts.app')]
#[Title('Pages - Admin')]
class PageIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $page = Page::findOrFail($id);
        $page->delete();

        session()->flash('success', 'Page deleted successfully.');
    }

    public function render()
    {
        $pages = Page::query()
            ->with('author')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->orderBy('menu_order')
            ->orderBy('title')
            ->paginate(15);

        return view('livewire.admin.pages.page-index', [
            'pages' => $pages,
        ]);
    }
}
