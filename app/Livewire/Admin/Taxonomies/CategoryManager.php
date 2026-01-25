<?php

namespace App\Livewire\Admin\Taxonomies;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layout')]
#[Title('Categories - Admin')]
class CategoryManager extends Component
{
    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:255')]
    public string $slug = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $description = '';

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
     * Create a new category.
     */
    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        Category::create([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
        ]);

        $this->resetForm();
        session()->flash('success', 'Category created successfully.');
    }

    /**
     * Populate form for editing.
     */
    public function edit(Category $category): void
    {
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
    }

    /**
     * Update an existing category.
     */
    public function update(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $this->editingId,
            'description' => 'nullable|string|max:1000',
        ]);

        $category = Category::findOrFail($this->editingId);
        $category->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
        ]);

        $this->resetForm();
        session()->flash('success', 'Category updated successfully.');
    }

    /**
     * Cancel editing mode.
     */
    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    /**
     * Delete a category if no posts attached.
     */
    public function delete(Category $category): void
    {
        if ($category->posts()->count() > 0) {
            session()->flash('error', 'Cannot delete category with posts attached. Remove posts first.');
            return;
        }

        $category->delete();
        session()->flash('success', 'Category deleted successfully.');
    }

    /**
     * Reset the form fields.
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.taxonomies.category-manager', [
            'categories' => Category::withCount('posts')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
