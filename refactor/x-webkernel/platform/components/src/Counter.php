<?php
namespace App\Liveview;

use Webkernel\Liveview\Component;

class Counter extends Component
{
    public int $count = 0;

    public function view(): string
    {
        return 'webkernel::counter';
    }

    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        $this->count--;
    }
}
