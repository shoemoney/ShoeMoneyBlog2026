<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
class UserForm extends Component
{
    public ?User $user = null;

    public string $name = '';
    public string $email = '';
    public string $author_name = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_admin = false;

    /**
     * Mount the component.
     */
    public function mount(?User $user = null): void
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
            $this->author_name = $user->author_name ?? '';
            $this->is_admin = (bool) $user->is_admin;
        }
    }

    /**
     * Check if we're editing an existing user.
     */
    public function isEditing(): bool
    {
        return $this->user !== null && $this->user->exists;
    }

    /**
     * Get the page title.
     */
    #[Title]
    public function getTitle(): string
    {
        return $this->isEditing() ? 'Edit User' : 'Create User';
    }

    /**
     * Get validation rules.
     */
    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ],
            'author_name' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['boolean'],
        ];

        // Password required on create, optional on edit
        if ($this->isEditing()) {
            $rules['password'] = ['nullable', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['required', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        return [
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters.',
        ];
    }

    /**
     * Save the user.
     */
    public function save(): void
    {
        $validated = $this->validate();

        if ($this->isEditing()) {
            // Update existing user
            $this->user->name = $validated['name'];
            $this->user->email = $validated['email'];
            $this->user->author_name = $validated['author_name'] ?: null;
            $this->user->is_admin = $validated['is_admin'];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $this->user->password = $validated['password'];
            }

            $this->user->save();

            session()->flash('success', "User {$this->user->display_name} has been updated.");
        } else {
            // Create new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'author_name' => $validated['author_name'] ?: null,
                'password' => $validated['password'],
                'is_admin' => $validated['is_admin'],
            ]);

            session()->flash('success', "User {$user->display_name} has been created.");
        }

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.admin.users.user-form');
    }
}
