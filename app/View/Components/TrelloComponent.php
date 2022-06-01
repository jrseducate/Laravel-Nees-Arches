<?php

namespace App\View\Components;

use App\Models\TrelloBoard;
use Illuminate\View\Component;

class TrelloComponent extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return callable
     */
    public function render()
    {
        return function(array $data)
        {
            $category = $data['attributes']['category'];

            return view('components.trello');
        };
    }
}
