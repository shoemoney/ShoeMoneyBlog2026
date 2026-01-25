<?php

namespace App\Livewire\Search;

use App\Models\Post;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';
    public bool $showResults = false;

    /**
     * Called when query property updates.
     */
    public function updatedQuery(): void
    {
        $this->showResults = strlen($this->query) >= 2;
    }

    /**
     * Hide results when user selects a result.
     */
    public function selectResult(): void
    {
        $this->showResults = false;
        $this->query = '';
    }

    /**
     * Render the component with search results.
     */
    public function render()
    {
        $results = collect();

        if (strlen($this->query) >= 2) {
            $results = Post::search($this->query)
                ->take(5)
                ->get();
        }

        return view('livewire.search.search-bar', [
            'results' => $results,
        ]);
    }
}
