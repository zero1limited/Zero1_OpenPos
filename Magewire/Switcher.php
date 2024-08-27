<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;

class Switcher extends Component
{
    public $listeners = ['toggle'];

    public $loader = 'Please wait...';

    protected $blocks;

    public function __construct(
        $blocks = null
    ) {
        $this->blocks = $blocks;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function toggle($selectedBlock)
    {
        foreach($this->blocks as $mode => $block) {
            if($block == $selectedBlock) {
                $this->emitTo($block, '$set', 'isVisible', true);
                $this->dispatchSuccessMessage('You are now in: '.$mode);
            } else {
                $this->emitTo($block, '$set', 'isVisible', false);
            }
        }
    }
}
