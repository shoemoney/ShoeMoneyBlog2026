<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
#[Title('Edit Page - Admin')]
class PageEdit extends Component
{
    public Page $page;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public int $menu_order = 0;

    public function mount(Page $page): void
    {
        $this->page = $page;
        $this->title = $page->title ?? '';
        $this->slug = $page->slug ?? '';
        $this->content = $page->content ?? '';
        $this->menu_order = $page->menu_order ?? 0;
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'slug' => 'required|unique:pages,slug,' . $this->page->id . '|max:255',
            'content' => 'required',
            'menu_order' => 'integer|min:0',
        ];
    }

    public function updatedTitle(): void
    {
        if (empty($this->slug) || $this->slug === Str::slug($this->page->title)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function update(): void
    {
        $this->validate();

        $this->page->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'menu_order' => $this->menu_order,
        ]);

        session()->flash('success', 'Page updated successfully.');

        $this->redirect(route('admin.pages.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.pages.page-edit');
    }
}
