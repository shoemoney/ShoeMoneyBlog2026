<?php

namespace App\Livewire\Admin;

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
            'postCount' => Post::posts()->count(),
            'publishedCount' => Post::posts()->published()->count(),
            'draftCount' => Post::posts()->draft()->count(),
            'pageCount' => Post::pages()->count(),
            'userCount' => User::count(),
        ]);
    }
}
