<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Widget;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
#[Title('Widgets - Admin')]
class WidgetManager extends Component
{
    public ?int $editingId = null;
    public string $title = '';
    public string $type = 'html';
    public string $content = '';
    public bool $is_active = true;

    // Settings for specific widget types
    public int $recent_posts_count = 5;

    public function create(): void
    {
        $this->validate($this->validationRules());

        $maxPosition = Widget::max('position') ?? -1;

        Widget::create([
            'title' => $this->title,
            'type' => $this->type,
            'content' => $this->type === 'html' ? $this->content : null,
            'settings' => $this->buildSettings(),
            'position' => $maxPosition + 1,
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        session()->flash('success', 'Widget created successfully.');
    }

    public function edit(Widget $widget): void
    {
        $this->editingId = $widget->id;
        $this->title = $widget->title;
        $this->type = $widget->type;
        $this->content = $widget->content ?? '';
        $this->is_active = $widget->is_active;
        $this->recent_posts_count = $widget->settings['count'] ?? 5;
    }

    public function update(): void
    {
        $this->validate($this->validationRules());

        $widget = Widget::findOrFail($this->editingId);
        $widget->update([
            'title' => $this->title,
            'type' => $this->type,
            'content' => $this->type === 'html' ? $this->content : null,
            'settings' => $this->buildSettings(),
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        session()->flash('success', 'Widget updated successfully.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function delete(Widget $widget): void
    {
        $widget->delete();
        session()->flash('success', 'Widget deleted successfully.');
    }

    public function moveUp(int $id): void
    {
        $item = Widget::findOrFail($id);
        $previous = Widget::where('position', '<', $item->position)
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
        $item = Widget::findOrFail($id);
        $next = Widget::where('position', '>', $item->position)
            ->orderBy('position')
            ->first();

        if ($next) {
            $tempPos = $item->position;
            $item->update(['position' => $next->position]);
            $next->update(['position' => $tempPos]);
        }
    }

    private function buildSettings(): ?array
    {
        return match ($this->type) {
            'recent_posts' => ['count' => $this->recent_posts_count],
            default => null,
        };
    }

    private function validationRules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:html,recent_posts,categories,tags,about',
            'is_active' => 'boolean',
        ];

        if ($this->type === 'html') {
            $rules['content'] = 'required|string';
        }

        if ($this->type === 'recent_posts') {
            $rules['recent_posts_count'] = 'required|integer|min:1|max:20';
        }

        return $rules;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->type = 'html';
        $this->content = '';
        $this->is_active = true;
        $this->recent_posts_count = 5;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.settings.widget-manager', [
            'widgets' => Widget::orderBy('position')->get(),
        ]);
    }
}
