<?php

namespace App\Livewire\Admin\Taxonomies;

use App\Models\Tag;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layout')]
#[Title('Tags - Admin')]
class TagManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:255')]
    public string $slug = '';

    /**
     * Auto-generate slug from name.
     */
    public function updatedName(): void
    {
        if (!$this->editingId) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * Create a new tag.
     */
    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tags,slug',
        ]);

        Tag::create([
            'name' => $this->name,
            'slug' => $this->slug,
        ]);

        $this->resetForm();
        session()->flash('success', 'Tag created successfully.');
    }

    /**
     * Populate form for editing.
     */
    public function edit(Tag $tag): void
    {
        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->slug = $tag->slug;
    }

    /**
     * Update an existing tag.
     */
    public function update(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tags,slug,' . $this->editingId,
        ]);

        $tag = Tag::findOrFail($this->editingId);
        $tag->update([
            'name' => $this->name,
            'slug' => $this->slug,
        ]);

        $this->resetForm();
        session()->flash('success', 'Tag updated successfully.');
    }

    /**
     * Cancel editing mode.
     */
    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    /**
     * Delete a tag if no posts attached.
     */
    public function delete(Tag $tag): void
    {
        if ($tag->posts()->count() > 0) {
            session()->flash('error', 'Cannot delete tag with posts attached. Remove posts first.');
            return;
        }

        $tag->delete();
        session()->flash('success', 'Tag deleted successfully.');
    }

    /**
     * Reset the form fields.
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.taxonomies.tag-manager', [
            'tags' => Tag::withCount('posts')
                ->orderBy('name')
                ->paginate(50),
        ]);
    }
}
