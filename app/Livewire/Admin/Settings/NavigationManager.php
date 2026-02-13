<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
#[Title('Navigation - Admin')]
class NavigationManager extends Component
{
    public ?int $editingId = null;
    public string $label = '';
    public string $url = '';
    public string $type = 'custom';
    public ?int $linkable_id = null;
    public bool $is_active = true;

    public function updatedType(): void
    {
        $this->url = '';
        $this->linkable_id = null;
    }

    public function create(): void
    {
        $this->validate($this->validationRules());

        $maxPosition = MenuItem::max('position') ?? -1;

        $data = [
            'label' => $this->label,
            'type' => $this->type,
            'position' => $maxPosition + 1,
            'is_active' => $this->is_active,
        ];

        if ($this->type === 'custom') {
            $data['url'] = $this->url;
        } else {
            $data['linkable_id'] = $this->linkable_id;
            $data['linkable_type'] = $this->type === 'page'
                ? Page::class
                : Category::class;
        }

        MenuItem::create($data);

        $this->resetForm();
        session()->flash('success', 'Menu item created successfully.');
    }

    public function edit(MenuItem $menuItem): void
    {
        $this->editingId = $menuItem->id;
        $this->label = $menuItem->label;
        $this->url = $menuItem->url ?? '';
        $this->type = $menuItem->type;
        $this->linkable_id = $menuItem->linkable_id;
        $this->is_active = $menuItem->is_active;
    }

    public function update(): void
    {
        $this->validate($this->validationRules());

        $menuItem = MenuItem::findOrFail($this->editingId);

        $data = [
            'label' => $this->label,
            'type' => $this->type,
            'is_active' => $this->is_active,
        ];

        if ($this->type === 'custom') {
            $data['url'] = $this->url;
            $data['linkable_id'] = null;
            $data['linkable_type'] = null;
        } else {
            $data['url'] = null;
            $data['linkable_id'] = $this->linkable_id;
            $data['linkable_type'] = $this->type === 'page'
                ? Page::class
                : Category::class;
        }

        $menuItem->update($data);

        $this->resetForm();
        session()->flash('success', 'Menu item updated successfully.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function delete(MenuItem $menuItem): void
    {
        $menuItem->delete();
        session()->flash('success', 'Menu item deleted successfully.');
    }

    public function moveUp(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $previous = MenuItem::where('position', '<', $item->position)
            ->orderByDesc('position')
            ->first();

        if ($previous) {
            $tempPos = $item->position;
            $item->update(['position' => $previous->position]);
            $previous->update(['position' => $tempPos]);
        }
    }

    public function moveDown(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $next = MenuItem::where('position', '>', $item->position)
            ->orderBy('position')
            ->first();

        if ($next) {
            $tempPos = $item->position;
            $item->update(['position' => $next->position]);
            $next->update(['position' => $tempPos]);
        }
    }

    private function validationRules(): array
    {
        $rules = [
            'label' => 'required|string|max:255',
            'type' => 'required|in:custom,page,category',
            'is_active' => 'boolean',
        ];

        if ($this->type === 'custom') {
            $rules['url'] = 'required|string|max:255';
        } else {
            $rules['linkable_id'] = 'required|integer';
        }

        return $rules;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->label = '';
        $this->url = '';
        $this->type = 'custom';
        $this->linkable_id = null;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.settings.navigation-manager', [
            'menuItems' => MenuItem::orderBy('position')->get(),
            'pages' => Page::orderBy('title')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
