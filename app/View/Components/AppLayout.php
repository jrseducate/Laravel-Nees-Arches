<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $themes = [
            'Basic' => 'na-theme-basic',
            'Lavender' => 'na-theme-lavender',
            'Aqua' => 'na-theme-aqua',
        ];
        $theme = array_values($themes)[0];

        return view('layouts.app', [
            'themes' => $themes,
            'theme'  => $theme,
        ]);
    }
}
