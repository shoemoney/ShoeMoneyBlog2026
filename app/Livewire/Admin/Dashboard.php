<?php

namespace App\Livewire\Admin;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'postCount' => Post::count(),
            'publishedCount' => Post::published()->count(),
            'draftCount' => Post::draft()->count(),
            'pendingComments' => Comment::pending()->count(),
            'userCount' => User::count(),
        ]);
    }
}
