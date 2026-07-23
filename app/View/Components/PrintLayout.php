<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PrintLayout extends Component
{
    public ?int $orderId;

    public function __construct(?int $orderId = null)
    {
        $this->orderId = $orderId;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.print');
    }
}
