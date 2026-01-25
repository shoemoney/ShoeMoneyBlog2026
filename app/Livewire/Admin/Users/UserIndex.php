<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.layouts.app')]
#[Title('Users')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Reset pagination when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle admin status for a user.
     */
    public function toggleAdmin(User $user): void
    {
        // Cannot modify self
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot modify your own admin status.');
            return;
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $action = $user->is_admin ? 'granted to' : 'revoked from';
        session()->flash('success', "Admin access {$action} {$user->display_name}.");
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): void
    {
        // Cannot delete self
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        // Cannot delete another admin
        if ($user->is_admin) {
            session()->flash('error', 'You cannot delete another admin user.');
            return;
        }

        $name = $user->display_name;
        $user->delete();

        session()->flash('success', "User {$name} has been deleted.");
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $users = User::query()
            ->withCount('posts')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('author_name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.admin.users.user-index', [
            'users' => $users,
        ]);
    }
}
